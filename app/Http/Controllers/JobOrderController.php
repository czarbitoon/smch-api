<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JobOrder;
use App\Models\Report;
use Illuminate\Support\Facades\Auth;

class JobOrderController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = JobOrder::with(['report', 'assignedTo', 'device']);

        // Filter based on user role
        if ($user->role < 2) { // Staff and below
            $query->where('assigned_to', $user->id)
                  ->orWhereHas('report', function($q) use ($user) {
                      $q->where('office_id', $user->office_id);
                  });
        }

        // Apply filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }

        $jobOrders = $query->latest()->paginate(10);
        return response()->json($jobOrders);
    }

    public function store(Request $request)
    {
        $request->validate([
            'report_id' => 'required|exists:reports,id',
            'assigned_to' => 'required|exists:users,id',
            'job_type' => 'required|string',
            'description' => 'required|string',
            'priority' => 'required|in:low,medium,high,urgent'
        ]);

        $report = Report::findOrFail($request->report_id);
        
        $jobOrder = new JobOrder([
            'report_id' => $report->id,
            'assigned_to' => $request->assigned_to,
            'device_id' => $report->device_id,
            'job_type' => $request->job_type,
            'description' => $request->description,
            'priority' => $request->priority,
            'status' => 'pending',
            'started_at' => now()
        ]);

        $jobOrder->save();
        $report->status = 'in_progress';
        $report->save();

        return response()->json([
            'message' => 'Job order created successfully',
            'job_order' => $jobOrder->load(['report', 'assignedTo', 'device'])
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $jobOrder = JobOrder::findOrFail($id);
        $user = Auth::user();

        if ($user->role < 1 && $jobOrder->assigned_to !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'status' => 'sometimes|required|in:pending,in_progress,completed,cancelled',
            'work_performed' => 'required_if:status,completed|string|nullable',
            'parts_used' => 'sometimes|string|nullable',
            'labor_hours' => 'sometimes|numeric|nullable'
        ]);

        $jobOrder->fill($request->only([
            'status',
            'work_performed',
            'parts_used',
            'labor_hours'
        ]));

        if ($request->status === 'completed') {
            $jobOrder->completed_at = now();
            $jobOrder->report->status = 'completed';
            $jobOrder->report->save();
        }

        $jobOrder->save();

        return response()->json([
            'message' => 'Job order updated successfully',
            'job_order' => $jobOrder->load(['report', 'assignedTo', 'device'])
        ]);
    }
}