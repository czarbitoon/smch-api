<?php

// ReportsController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Report;

class ReportsController extends Controller
{
    public function store(Request $request)
    {
        $report = new Report();
        $report->title = $request->input('title');
        $report->description = $request->input('description');
        $report->save();
        return response()->json(['message' => 'Report added successfully']);
    }

    public function index()
    {
        $reports = Report::all();
        return response()->json($reports);
    }

    public function show($id)
    {
        $report = Report::find($id);
        return response()->json($report);
    }

    public function update(Request $request, $id)
    {
        $report = Report::find($id);
        $report->title = $request->input('title');
        $report->description = $request->input('description');
        $report->save();
        return response()->json(['message' => 'Report updated successfully']);
    }

    public function destroy($id)
    {
        $report = Report::find($id);
        $report->delete();
        return response()->json(['message' => 'Report deleted successfully']);
    }
}