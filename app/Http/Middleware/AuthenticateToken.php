<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticateToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle($request, Closure $next)
{
    if (!$request->header('Authorization')) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    $token = str_replace('Bearer ', '', $request->header('Authorization'));

    $user = User::where('api_token', hash('sha256', $token))->first();

    if (!$user) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    Auth::login($user);

    return $next($request);
}
}
