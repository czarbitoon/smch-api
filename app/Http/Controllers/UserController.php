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
        
        if (!$user || $user->type !== 0) { // Check if user is a regular user (type = 0)
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
}