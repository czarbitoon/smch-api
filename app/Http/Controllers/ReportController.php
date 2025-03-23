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
                    $allowedStatuses = ['pending', 'in_progress', 'completed', 'cancelled'];
                    if (!in_array(strtolower($value), $allowedStatuses)) {
                        $fail('The status must be one of: ' . implode(', ', $allowedStatuses));
                    }
                }],
                'priority' => ['required', 'string', function($attribute, $value, $fail) {
                    $allowedPriorities = ['Low', 'Medium', 'High', 'Critical'];
                    if (!in_array(ucfirst(strtolower($value)), $allowedPriorities)) {
                        $fail('The priority must be one of: ' . implode(', ', $allowedPriorities));
                    }
                }]
            ], [
                'title.required' => 'The report title is required',
                'title.max' => 'The report title must not exceed 255 characters',
                'description.required' => 'The report description is required',
                'device_id.required' => 'A device must be selected for the report',
                'device_id.exists' => 'The selected device does not exist',
                'status.in' => 'Invalid status. Must be one of: pending, in_progress, completed, cancelled',
                'priority.required' => 'Priority level is required',
                'priority.in' => 'Priority must be one of: Low, Medium, High, Critical'
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

            // Create and save the report using mass assignment
            $report = Report::create([
                'title' => $validatedData['title'],
                'description' => $validatedData['description'],
                'device_id' => $device->id,
                'user_id' => $user->id,
                'office_id' => $device->office_id,
                'status' => strtolower($status),
                'priority' => $priority
            ]);

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

        // Filter based on user type
        if (!$user) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }

        if ($user->type >= 2) { // Admin (2) and Superadmin (3) can see all reports
            // Admin can see all reports
        } elseif ($user->type === 1) { // Staff (1)
            // Staff can see reports from their office
            $query->where('office_id', $user->office_id);
        } else { // Regular users (0)
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
            if ($user->type < 1) {
                return response()->json(['error' => 'Unauthorized. Only staff and admin can update reports'], 403);
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
            if ($user->type < 1 && $report->user_id !== $user->id) {
                return response()->json([
                    'error' => 'Unauthorized. Only staff, admin, or the report creator can delete reports'
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

            if ($user->type < 1) {
                return response()->json(['error' => 'Unauthorized. Only staff and admin can resolve reports'], 403);
            }

            $report = Report::findOrFail($id);

            // Check if report can be resolved
            if ($report->status === 'resolved') {
                return response()->json([
                    'error' => 'Report is already resolved'
                ], 400);
            }

            if (!in_array($report->status, ['pending', 'in_progress'])) {
                return response()->json([
                    'error' => 'Cannot resolve a report that is ' . $report->status
                ], 400);
            }

            $validatedData = $request->validate([
                'resolution_notes' => 'required|string|max:1000'
            ]);

            DB::beginTransaction();
            try {
                $report->status = 'resolved';
                $report->resolved_by = $user->id;
                $report->resolution_notes = $validatedData['resolution_notes'];
                $report->resolved_at = now();
                $report->save();

                // Create notification for the report owner
                Notification::create([
                    'title' => 'Report Resolved',
                    'message' => "Your report '{$report->title}' has been resolved.",
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
        } catch (\Illuminate\Database\EloquentModelNotFoundException $e) {
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
}