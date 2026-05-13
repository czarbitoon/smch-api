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
        $origin = $request->header('origin') ?: $request->header('Origin');

        // Get allowed origins from config
        $allowedOrigins = array_filter(array_map('trim', explode(',', env('ALLOWED_ORIGINS', 'http://localhost:5173'))));
        
        $isAllowed = false;

        // Check exact matches
        if (in_array($origin, $allowedOrigins)) {
            $isAllowed = true;
        }

        // Check patterns
        if (!$isAllowed) {
            $patterns = [
                '#^https://.*\.vercel\.app$#i',
                '#^https://.*\.ngrok(?:-free)?\.dev$#i',
                '#^https?://localhost(:\d+)?$#i',
            ];
            
            foreach ($patterns as $pattern) {
                if ($origin && preg_match($pattern, $origin)) {
                    $isAllowed = true;
                    break;
                }
            }
        }

        // Handle preflight requests
        if ($request->isMethod('OPTIONS')) {
            if ($isAllowed) {
                return response('', 200)
                    ->header('Access-Control-Allow-Origin', $origin)
                    ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS')
                    ->header('Access-Control-Allow-Headers', 'Content-Type, X-Requested-With, Authorization, Accept, Origin, X-XSRF-TOKEN, X-CSRF-TOKEN, ngrok-skip-browser-warning')
                    ->header('Access-Control-Expose-Headers', 'Authorization, X-XSRF-TOKEN, Set-Cookie')
                    ->header('Access-Control-Allow-Credentials', 'true')
                    ->header('Access-Control-Max-Age', '0');
            }
            return response('Forbidden', 403);
        }

        // Handle actual requests
        $response = $next($request);

        if ($isAllowed && $origin) {
            $response->header('Access-Control-Allow-Origin', $origin);
            $response->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
            $response->header('Access-Control-Allow-Headers', 'Content-Type, X-Requested-With, Authorization, Accept, Origin, X-XSRF-TOKEN, X-CSRF-TOKEN, ngrok-skip-browser-warning');
            $response->header('Access-Control-Expose-Headers', 'Authorization, X-XSRF-TOKEN, Set-Cookie');
            $response->header('Access-Control-Allow-Credentials', 'true');
            $response->header('Access-Control-Max-Age', '0');
        }

        return $response;
    }
}

