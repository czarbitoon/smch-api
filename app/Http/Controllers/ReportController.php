<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Report;
use App\Models\Device;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function addReport(Request $request)
    {
        $request->validate([
            'description' => 'required|string',
            'device_id' => 'required|exists:devices,id',
        ]);

        $device = Device::findOrFail($request->device_id);
        $user = Auth::user();

        $report = new Report();
        $report->description = $request->description;
        $report->device_id = $device->id;
        $report->user_id = $user->id;
        $report->office_id = $device->office_id;
        $report->status = 'pending';
        $report->save();

        return response()->json([
            'message' => 'Report added successfully',
            'report' => $report->load(['device', 'user', 'office'])
        ], 201);
    }

    public function getReports(Request $request)
    {
        $user = Auth::user();
        $query = Report::with(['device', 'user', 'office']);

        // Filter based on user role
        if ($user->role >= 2) { // Admin (2) and Superadmin (3) can see all reports
            // Admin can see all reports
        } elseif ($user->role === 1) { // Staff (1)
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
        if ($user->role < 1) { // Check if user is at least staff level (1)
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

    public function resolve(Request $request, $id)
    {
        $report = Report::find($id);
        if (!$report) {
            return response()->json(['message' => 'Report not found'], 404);
        }

        $report->status = 'resolved';
        $report->resolved_by = $request->user()->id; // Set the resolver
        $report->save();
        return response()->json(['message' => 'Report resolved successfully']);
    }
}

