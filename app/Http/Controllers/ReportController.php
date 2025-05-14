<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Report;
use App\Models\Device;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
use App\Events\ReportSubmitted;

class ReportController extends Controller
{
    public function addReport(Request $request)
    {
        try {
            // Validate the request
            $validatedData = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'device_id' => 'required|exists:devices,id',
                'status' => ['sometimes', 'string', function($attribute, $value, $fail) {
                    $allowedStatuses = ['pending', 'in_progress', 'resolved', 'closed'];
                    if (!in_array(strtolower($value), $allowedStatuses)) {
                        $fail('The status must be one of: ' . implode(', ', $allowedStatuses));
                    }
                }],
                'priority' => ['required', 'string', function($attribute, $value, $fail) {
                    $allowedPriorities = ['Low', 'Medium', 'High', 'Critical'];
                    if (!in_array(ucfirst(strtolower($value)), $allowedPriorities)) {
                        $fail('The priority must be one of: ' . implode(', ', $allowedPriorities));
                    }
                }],
                'report_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ], [
                'title.required' => 'The report title is required',
                'title.max' => 'The report title must not exceed 255 characters',
                'description.required' => 'The report description is required',
                'device_id.required' => 'A device must be selected for the report',
                'device_id.exists' => 'The selected device does not exist',
                'status.in' => 'Invalid status. Must be one of: pending, in_progress, completed, cancelled',
                'priority.required' => 'Priority level is required',
                'priority.in' => 'Priority must be one of: Low, Medium, High, Critical',
                'report_image.image' => 'The file must be an image',
                'report_image.mimes' => 'The image must be a file of type: jpeg, png, jpg, gif',
                'report_image.max' => 'The image must not be larger than 2MB',
            ]);

            // Find the device and verify it exists
            $device = Device::findOrFail($validatedData['device_id']);
            $user = Auth::user();

            if (!$user) {
                return response()->json(['error' => 'User not authenticated'], 401);
            }

            // Check if priority is valid
            if (isset($validatedData['priority']) && !in_array($validatedData['priority'], ['Low', 'Medium', 'High', 'Critical'])) {
                return response()->json(['error' => 'Invalid priority level'], 422);
            }

            // Normalize status and priority case
            $status = $validatedData['status'] ?? 'pending';
            $priority = ucfirst(strtolower($validatedData['priority']));

            // Handle report image upload if provided
            $reportImagePath = null;
            if ($request->hasFile('report_image')) {
                // Use ImageController logic for upload
                $image = $request->file('report_image');
                $filename = \Illuminate\Support\Str::uuid() . '.' . $image->getClientOriginalExtension();
                $path = $image->storeAs('report_images', $filename, 'public');
                $reportImagePath = $path;
            }

            // Get device image URL if available
            $deviceImageUrl = null;
            if ($device->image_url) {
                $deviceImageUrl = $device->image_url;
            }

            // Create and save the report using mass assignment
            $report = Report::create([
                'title' => $validatedData['title'],
                'description' => $validatedData['description'],
                'device_id' => $device->id,
                'user_id' => $user->id,
                'office_id' => $user->office_id,
                'status' => strtolower($status),
                'priority' => $priority,
                'report_image' => $reportImagePath,
                'device_image_url' => $deviceImageUrl,
            ]);

            // Dispatch event for broadcasting (real-time notification)
            event(new ReportSubmitted($report));

            // Synchronize device status with report status
            if (in_array(strtolower($status), ['pending', 'in_progress'])) {
                $device->status = 'maintenance';
                $device->save();
            } elseif (strtolower($status) === 'completed' || strtolower($status) === 'cancelled') {
                $device->status = 'active';
                $device->save();
            }

            // Dispatch the ReportSubmitted event
            try {
                event(new ReportSubmitted($report));
            } catch (\Exception $e) {
                // Log the notification error but don't fail the request
                \Log::error('Failed to send report notification: ' . $e->getMessage());
            }

            return response()->json([
                'message' => 'Report added successfully',
                'report' => $report->load(['device', 'user', 'office'])
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::info('Report validation failed: ' . json_encode([
                'input' => $request->all(),
                'errors' => $e->errors()
            ]));
            return response()->json([
                'error' => 'Validation error',
                'details' => $e->errors()
            ], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Device not found'
            ], 404);
        } catch (\Exception $e) {
            \Log::error('Error in addReport: ' . $e->getMessage());
            return response()->json([
                'error' => 'An unexpected error occurred while submitting the report'
            ], 500);
        }
    }

    public function getReports(Request $request)
    {
        $user = Auth::user();
        $query = Report::with(['device', 'user', 'office']);

        // Filter based on user role
        if (!$user) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }

        if (in_array($user->role, ['admin', 'superadmin'])) { // Admin and Superadmin can see all reports
            // Admin and Superadmin can see all reports
        } elseif ($user->role === 'staff') { // Staff
            // Staff can see reports from their office
            $query->where('office_id', $user->office_id);
        } else { // Regular users
            // Regular users can only see their own reports
            $query->where('user_id', $user->id);
        }

        $reports = $query->latest()->get();
        return response()->json($reports);
    }

    public function update(Request $request, $id)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['error' => 'User not authenticated'], 401);
            }

            $report = Report::findOrFail($id);

            // Only staff and admin can update reports
            if (!in_array($user->role, ['staff', 'admin', 'superadmin'])) {
                return response()->json(['error' => 'Unauthorized. Only staff, admin, or superadmin can update reports'], 403);
            }

            $validatedData = $request->validate([
                'status' => 'required|in:pending,in_progress,completed,cancelled',
                'resolution_notes' => 'required_if:status,completed|string|nullable',
                'priority' => 'sometimes|string|in:Low,Medium,High,Critical'
            ], [
                'status.required' => 'The status field is required',
                'status.in' => 'Invalid status. Must be one of: pending, in_progress, completed, cancelled',
                'resolution_notes.required_if' => 'Resolution notes are required when status is completed',
                'priority.in' => 'Priority must be one of: Low, Medium, High, Critical'
            ]);

            $report->status = $validatedData['status'];
            if ($validatedData['status'] === 'completed') {
                $report->resolution_notes = $validatedData['resolution_notes'];
                $report->resolved_by = $user->id;
                $report->resolved_at = now();
            }

            if (isset($validatedData['priority'])) {
                $report->priority = $validatedData['priority'];
            }

            $report->save();

            // Synchronize device status with report status
            $device = $report->device;
            if (in_array(strtolower($report->status), ['pending', 'in_progress'])) {
                $device->status = 'maintenance';
                $device->save();
            } elseif (strtolower($report->status) === 'completed' || strtolower($report->status) === 'cancelled') {
                $device->status = 'active';
                $device->save();
            }

            return response()->json([
                'message' => 'Report updated successfully',
                'report' => $report->load(['device', 'user', 'office'])
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::info('Report validation failed: ' . json_encode([
                'input' => $request->all(),
                'errors' => $e->errors()
            ]));
            return response()->json([
                'error' => 'Validation error',
                'details' => $e->errors()
            ], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Report not found'
            ], 404);
        } catch (\Exception $e) {
            \Log::error('Error in update: ' . $e->getMessage());
            return response()->json([
                'error' => 'An unexpected error occurred while updating the report'
            ], 500);
        }
    }
    public function delete($id)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['error' => 'User not authenticated'], 401);
            }

            $report = Report::findOrFail($id);

            // Only staff, admin, and the report creator can delete reports
            if (!in_array($user->role, ['staff', 'admin', 'superadmin']) && $report->user_id !== $user->id) {
                return response()->json([
                    'error' => 'Unauthorized. Only staff, admin, superadmin, or the report creator can delete reports'
                ], 403);
            }

            $report->delete();
            return response()->json(['message' => 'Report deleted successfully']);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Report not found'
            ], 404);
        } catch (\Exception $e) {
            \Log::error('Error in delete: ' . $e->getMessage());
            return response()->json([
                'error' => 'An unexpected error occurred while deleting the report'
            ], 500);
        }
    }


    public function resolveReport(Request $request, $id)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['error' => 'User not authenticated'], 401);
            }

            // Only staff and admin can resolve reports
            if (!in_array($user->role, ['staff', 'admin', 'superadmin'])) {
                return response()->json(['error' => 'Unauthorized. Only staff, admin, or superadmin can resolve reports'], 403);
            }

            $report = Report::findOrFail($id);

            // Only allow resolving if status is pending or in_progress
            if (!in_array($report->status, ['pending', 'in_progress'])) {
                return response()->json([
                    'error' => 'Cannot resolve a report that is ' . $report->status
                ], 400);
            }

            $validatedData = $request->validate([
                'status' => 'required|in:completed,cancelled',
                'resolution_notes' => 'required_if:status,completed|string|nullable|max:1000'
            ], [
                'status.required' => 'The status field is required',
                'status.in' => 'Status must be either completed or cancelled',
                'resolution_notes.required_if' => 'Resolution notes are required when status is completed',
            ]);

            DB::beginTransaction();
            try {
                $report->status = $validatedData['status'];
                $report->resolved_by = $user->id;
                $report->resolved_at = now();
                if ($validatedData['status'] === 'completed') {
                    $report->resolution_notes = $validatedData['resolution_notes'];
                } else {
                    $report->resolution_notes = null;
                }
                $report->save();

                // Synchronize device status with report status
                $device = $report->device;
                if (in_array($report->status, ['pending', 'in_progress'])) {
                    $device->status = 'maintenance';
                } elseif (in_array($report->status, ['completed', 'cancelled'])) {
                    $device->status = 'active';
                }
                $device->save();

                // Create notification for the report owner
                Notification::create([
                    'title' => 'Report ' . ucfirst($report->status),
                    'message' => "Your report '{$report->title}' has been marked as {$report->status}.",
                    'user_id' => $report->user_id,
                    'report_id' => $report->id,
                    'read' => false
                ]);

                DB::commit();

                return response()->json([
                    'message' => 'Report resolved successfully',
                    'report' => $report->fresh(['device', 'user', 'office', 'resolvedByUser'])
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Report not found'], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'details' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error in resolveReport: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while resolving the report'], 500);
        }
    }
    public function show($id)
    {
        $user = Auth::user();
        try {
            $report = Report::with(['device', 'user', 'office'])->findOrFail($id);
            // Authorization: Admins (role 2+) can view any report; others only their own
            if ($user->role < 2 && $report->user_id !== $user->id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            return response()->json(['report' => $report], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Report not found'], 404);
        } catch (\Exception $e) {
            \Log::error('Error in show report: ' . $e->getMessage());
            return response()->json(['error' => 'An unexpected error occurred'], 500);
        }
        // Log the report status and description for debugging
        \Log::info('Report Status: ' . $report->status . ', Description: ' . $report->description);
    }
}
