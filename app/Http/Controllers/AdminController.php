<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Device;
use App\Models\Report;
use App\Models\Office;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function __construct()
    {
        // Middleware is handled in routes/api.php
    }

    public function stats()
    {
        try {
            $stats = [
                'users' => User::count(),
                'devices' => Device::count(),
                'reports' => Report::whereIn('status', ['pending','in_progress'])->count(),
                'offices' => Office::count()
            ];

            return response()->json($stats);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch statistics'], 500);
        }
    }
}
