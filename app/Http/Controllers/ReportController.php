<?php

// ReportsController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Report;

class ReportController extends Controller
{
    public function addReport(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'description' => 'required|string',
            'device_id' => 'required|exists:devices,id',
        ]);

        $report = new Report();
        $report->title = $request->input('title');
        $report->description = $request->input('description');
        $report->device_id = $request->input('device_id');
        $report->user_id = $request->user()->id; // Set the reporter
        $report->save();
        return response()->json(['message' => 'Report added successfully']);
    }

    public function getReports()
    {
        $reports = Report::all();
        return response()->json($reports);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string',
            'description' => 'required|string',
        ]);

        $report = Report::find($id);
        if (!$report) {
            return response()->json(['message' => 'Report not found'], 404);
        }

        $report->title = $request->input('title');
        $report->description = $request->input('description');
        $report->save();
        return response()->json(['message' => 'Report updated successfully']);
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
