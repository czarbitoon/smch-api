<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Report;
use App\Models\Device;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
                'status' => 'sometimes|string|in:pending,in_progress,resolved,closed'
            ]);

            // Find the device and verify it exists
            $device = Device::findOrFail($validatedData['device_id']);
            $user = Auth::user();

            if (!$user) {
                return response()->json(['error' => 'User not authenticated'], 401);
            }

            // Create and save the report using mass assignment
            $report = Report::create([
                'title' => $validatedData['title'],
                'description' => $validatedData['description'],
                'device_id' => $device->id,
                'user_id' => $user->id,
                'office_id' => $device->office_id,
                'status' => $validatedData['status'] ?? 'pending'
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
        $user = Auth::user();
        $report = Report::findOrFail($id);

        // Only staff and admin can update reports
        if ($user->type < 1) { // Check if user is at least staff level (1)
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'status' => 'required|in:pending,in_progress,resolved,closed',
            'resolution_notes' => 'required_if:status,resolved|string|nullable'
        ]);

        $report->status = $request->status;
        if ($request->status === 'resolved') {
            $report->resolution_notes = $request->resolution_notes;
            $report->resolved_by = $user->id;
            $report->resolved_at = now();
        }
        $report->save();

        return response()->json([
            'message' => 'Report updated successfully',
            'report' => $report->load(['device', 'user', 'office'])
        ]);
    }
    public function delete($id)
    {
        $report = Report::find($id);
        if (!$report) {
            return response()->json(['message' => 'Report not found'], 404);
        }

        $report->delete();
        return response()->json(['message' => 'Report deleted successfully']);
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

