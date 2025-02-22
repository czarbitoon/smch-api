<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = Auth::user();
        if ($user->type < 2) { // Check if user is not admin (type < 2)
            return response()->json(['error' => 'Access denied. Admin privileges required.'], 403);
        }

        return $next($request);
    }
}
