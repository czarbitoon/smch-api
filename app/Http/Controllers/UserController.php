<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Report;
use App\Models\Device;

class UserController extends Controller
{
    public function stats()
    {
        $user = Auth::user();

        if (!$user || $user->user_role !== 'user') { // Check if user is a regular user (role = 'user')
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Get user-specific statistics
        $stats = [
            'activeReports' => Report::where('user_id', $user->id)
                                    ->where('status', 'active')
                                    ->count(),
            'totalDevices' => Device::where('office_id', $user->office_id)
                                    ->count(),
            'recentActivities' => Report::where('user_id', $user->id)
                                    ->where('created_at', '>=', now()->subDays(7))
                                    ->count()
        ];

        return response()->json($stats, 200);
    }

    public function index()
    {
        // Only allow access for admin or staff
        $user = Auth::user();
        if (!$user || !in_array($user->user_role, ['admin', 'staff'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Fetch all users, optionally filter by role if needed
        $users = \App\Models\User::all();
        return response()->json(['data' => $users], 200);
    }
}
