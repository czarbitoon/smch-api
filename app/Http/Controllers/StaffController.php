<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StaffController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('staff');
    }

    public function stats()
    {
        try {
            $stats = [
                'activeDevices' => Device::where('status', 'active')->count(),
                'pendingReports' => Report::where('status', 'pending')->count(),
                'resolvedReports' => Report::where('status', 'resolved')->count()
            ];

            return response()->json($stats);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch statistics'], 500);
        }
    }
}
