<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CorsMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $origin = $request->headers->get('origin');

        // Get allowed origins from config
        $allowedOrigins = config('cors.allowed_origins', []);
        $allowedPatterns = config('cors.allowed_origins_patterns', []);

        $isAllowed = false;

        // Check exact matches
        if (in_array($origin, $allowedOrigins)) {
            $isAllowed = true;
        }

        // Check regex patterns
        if (!$isAllowed) {
            foreach ($allowedPatterns as $pattern) {
                if (preg_match($pattern, $origin)) {
                    $isAllowed = true;
                    break;
                }
            }
        }

        // Allow localhost for development
        if (!$isAllowed && preg_match('#^https?://localhost(:\d+)?$#', $origin)) {
            $isAllowed = true;
        }

        // If preflight request
        if ($request->isMethod('OPTIONS')) {
            $response = response('', 200);
        } else {
            $response = $next($request);
        }

        // Add CORS headers if allowed
        if ($isAllowed) {
            $response->header('Access-Control-Allow-Origin', $origin);
            $response->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
            $response->header('Access-Control-Allow-Headers', 'Content-Type, X-Requested-With, Authorization, Accept, Origin, X-XSRF-TOKEN, X-CSRF-TOKEN, ngrok-skip-browser-warning');
            $response->header('Access-Control-Expose-Headers', 'Authorization, X-XSRF-TOKEN');
            $response->header('Access-Control-Allow-Credentials', 'true');
            $response->header('Access-Control-Max-Age', '0');
        }

        return $response;
    }
}
