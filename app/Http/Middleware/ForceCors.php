<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ForceCors
{
   public function handle($request, $next)
{
    // Handle OPTIONS (Preflight) first so it never hits the crashing core
    if ($request->isMethod('OPTIONS')) {
        return response('', 200)
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, X-XSRF-TOKEN, ngrok-skip-browser-warning')
            ->header('Access-Control-Allow-Credentials', 'true');
    }

    $response = $next($request);

    // Add headers to successful responses
    return $response
        ->header('Access-Control-Allow-Origin', '*')
        ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, X-XSRF-TOKEN, ngrok-skip-browser-warning')
        ->header('Access-Control-Allow-Credentials', 'true');
}
}