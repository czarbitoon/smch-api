<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Device;
use App\Models\Report;
use App\Models\Office;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        try {
            $credentials = $request->only(['email', 'password']);

            if (!Auth::attempt($credentials)) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $user = Auth::user();
            $token = $user->createToken('authToken')->plainTextToken;

            return response()->json([
                'access_token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => 60 * 60, // Set expiration time (1 hour)
                'type' => $user->type, // Include user type for frontend routing
                'office_id' => $user->office_id
            ]);
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function register(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8|confirmed',
                'type' => 'required|integer|in:0,1,2,3', // Validate type input (0=user, 1=staff, 2=admin, 3=superadmin)
                'office_id' => 'required|exists:offices,id' // Validate office_id
            ]);

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'type' => $request->type,
                'office_id' => $request->office_id
            ]);

            return response()->json([
                'message' => 'User created successfully',
                'user' => $user
            ], 201);
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();
            return response()->json(['message' => 'Successfully logged out']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Logout failed: ' . $e->getMessage()], 500);
        }
    }

    public function profile()
    {
        $user = Auth::user(); // Get the authenticated user
        return response()->json([
            'user' => $user,
            'type' => $user->type,
            'office_id' => $user->office_id
        ], 200);
    }

    public function getAdminStats()
    {
        // Check if user is admin
        $user = Auth::user();
        if (!$user || !in_array($user->type, [2, 3])) { // Check for admin (2) or superadmin (3)
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Get counts from each model
        $stats = [
            'users' => User::count(),
            'devices' => Device::count(),
            'reports' => Report::count(),
            'offices' => Office::count()
        ];

        return response()->json($stats, 200);
    }
}
