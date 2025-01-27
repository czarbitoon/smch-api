<?php

// ReportsController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Report;

class ReportController extends Controller
{
    public function addReport(Request $request)
    {
        $report = new Report();
        $report->title = $request->input('title');
        $report->description = $request->input('description');
        $report->device_id = $request->input('device_id');
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
        $report->title = $request->input('title');
        $report->description = $request->input('description');
        $report->save();
        return response()->json(['message' => 'Report updated successfully']);
    }

    public function delete($id)
    {
        $report = Report::find($id);
        $report->delete();
        return response()->json(['message' => 'Report deleted successfully']);
    }

    public function resolve($id)
    {
        $report = Report::find($id);
        $report->status = 'resolved';
        $report->save();
        return response()->json(['message' => 'Report resolved successfully']);
    }
}
