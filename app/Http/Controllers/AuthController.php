<?php

// AuthController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

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
        $refreshToken = $user->createToken('authToken')->refreshToken;
        $expirationTime = $user->createToken('authToken')->expires_in;

        return response()->json([
            'access_token' => $token,
            'refresh_token' => $refreshToken,
            'expiration_time' => $expirationTime,
            'token_type' => 'Bearer',
        ]);
    } catch (ValidationException $e) {
        return response()->json(['error' => $e->getMessage()], 400);
    }
}
    public function refreshToken(Request $request)
    {
        $request->validate([
            'refresh_token' => 'required',
        ]);

        $token = $request->user()->createToken('authToken', ['*'])->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => Auth::guard('web')->factory()->getTTL() * 60,
        ]);
    }

    public function logout()
    {
        Auth::guard('api')->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    public function register(Request $request)
    {
        try {
            $this->validate($request, [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'password' => ['required', 'string', 'min:8', 'confirmed'],
                'role' => ['required', 'string'],
                'office_id' => 'required|exists:offices,id',
            ]);

            $validatedData = $request->only(['name', 'email', 'password', 'role', 'office_id']);
            $validatedData['password'] = bcrypt($validatedData['password']);

            $user = User::create($validatedData);

            auth()->login($user);

            return response()->json([
                'message' => 'Registration successful'
            ], 200);

        } catch (ValidationException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::guard('api')->factory()->getTTL() * 60
        ]);
    }

    public function profile()
    {
        $user = Auth::user(); // Get the authenticated user

        return response()->json(['user' => $user], 200);
    }
}