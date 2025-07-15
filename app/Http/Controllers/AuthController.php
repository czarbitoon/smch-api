<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
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
            // Log the incoming request
            Log::info('Login attempt', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'has_email' => $request->has('email'),
                'has_password' => $request->has('password')
            ]);

            // Validate input
            $request->validate([
                'email' => 'required|email',
                'password' => 'required'
            ]);

            $credentials = $request->only(['email', 'password']);

            // Check if database connection is working
            try {
                DB::connection()->getPdo();
                Log::info('Database connection successful');
            } catch (\Exception $e) {
                Log::error('Database connection failed', ['error' => $e->getMessage()]);
                return response()->json(['error' => 'Database connection failed'], 500);
            }

            if (!Auth::attempt($credentials)) {
                Log::warning('Failed login attempt for email: ' . $credentials['email']);
                return response()->json(['error' => 'Invalid credentials'], 401);
            }

            $user = Auth::user();
            Log::info('User logged in successfully', ['user_id' => $user->id, 'email' => $user->email]);

            $token = $user->createToken('authToken')->plainTextToken;

            return response()->json([
                'access_token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => 60 * 60, // Set expiration time (1 hour)
                'user_role' => $user->user_role, // Include user role for frontend routing
                'office_id' => $user->office_id
            ]);
        } catch (ValidationException $e) {
            Log::error('Validation error in login', ['error' => $e->getMessage()]);
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            Log::error('Unexpected error in login', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    public function register(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8|confirmed',
                'user_role' => 'required|string|in:user,staff,admin,superadmin', // Validate user_role input
                'office_id' => 'required|exists:offices,id' // Validate office_id
            ]);

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'user_role' => $request->user_role,
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
            $user = $request->user();
            $request->user()->currentAccessToken()->delete();
            Log::info('User logged out', ['user_id' => $user->id]);
            return response()->json(['message' => 'Successfully logged out']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Logout failed: ' . $e->getMessage()], 500);
        }
    }

    public function profile()
    {
        $user = Auth::user(); // Get the authenticated user
        return response()->json([
            'user_role' => $user ? $user->user_role : null,
            'office_id' => $user ? $user->office_id : null,
            'user' => $user
        ], 200);
    }

    public function getAdminStats()
    {
        // Check if user is admin
        $user = Auth::user();
        if (!$user || !in_array($user->user_role, ['admin', 'superadmin'])) { // Check for admin or superadmin
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

    // Password reset request (send reset link)
    public function sendPasswordResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);
        $status = \Password::sendResetLink(
            $request->only('email')
        );
        if ($status === \Password::RESET_LINK_SENT) {
            return response()->json(['message' => __($status)]);
        }
        return response()->json(['error' => __($status)], 400);
    }

    // Password reset (using token)
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email|exists:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);
        $status = \Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->password = Hash::make($password);
                $user->save();
            }
        );
        if ($status === \Password::PASSWORD_RESET) {
            return response()->json(['message' => __($status)]);
        }
        return response()->json(['error' => __($status)], 400);
    }

    // Change password (authenticated)
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|string|min:8|confirmed',
        ]);
        $user = $request->user();
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['error' => 'Current password is incorrect'], 400);
        }
        $user->password = Hash::make($request->new_password);
        $user->save();
        return response()->json(['message' => 'Password changed successfully']);
    }

    // List users (admin only)
    public function listUsers(Request $request)
    {
        $user = $request->user();
        if (!in_array($user->user_role, ['admin', 'superadmin'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $users = User::paginate(20);
        return response()->json($users);
    }

    // Update user details (admin only)
    public function updateUser(Request $request, $id)
    {
        $user = $request->user();
        if (!in_array($user->user_role, ['admin', 'superadmin'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $target = User::findOrFail($id);
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|max:255|unique:users,email,' . $id,
            'user_role' => 'sometimes|string|in:user,staff,admin,superadmin',
            'office_id' => 'sometimes|exists:offices,id',
        ]);
        $target->update($request->only(['name', 'email', 'user_role', 'office_id']));
        return response()->json(['message' => 'User updated', 'user' => $target]);
    }

    // Change user role (admin only)
    public function changeUserRole(Request $request, $id)
    {
        $user = $request->user();
        if (!in_array($user->user_role, ['admin', 'superadmin'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $target = User::findOrFail($id);
        $request->validate([
            'user_role' => 'required|string|in:user,staff,admin,superadmin',
        ]);
        $target->user_role = $request->user_role;
        $target->save();
        return response()->json(['message' => 'User role updated', 'user' => $target]);
    }

    // Deactivate user (admin only)
    public function deactivateUser(Request $request, $id)
    {
        $user = $request->user();
        if (!in_array($user->user_role, ['admin', 'superadmin'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $target = User::findOrFail($id);
        $target->active = false;
        $target->save();
        return response()->json(['message' => 'User deactivated']);
    }
}
