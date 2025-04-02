<?php

// app/Http/Controllers/ProfileController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Office;
use Illuminate\Support\Facades\Log;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'User not authenticated'], 401);
        }
        return response()->json([
            'name' => $user->name,
            'email' => $user->email,
            'profile_picture' => $user->profile_picture,
            'office_id' => $user->office_id,
            'office_name' => $user->office ? $user->office->name : null,
        ]);
    }

    public function uploadPicture(Request $request)
    {
        $request->validate([
            'profile_picture' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'User not authenticated'], 401);
        }

        // Delete the old profile picture if it exists
        if ($user->profile_picture) {
            Storage::delete($user->profile_picture);
        }

        // Store the new profile picture
        $path = $request->file('profile_picture')->store('profile_pictures', 'public');

        // Update the user's profile picture
        $user->profile_picture = $path;
        $user->save();

        return response()->json([
            'message' => 'Profile picture uploaded successfully',
            'profile_picture' => $path,
        ]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'User not authenticated'], 401);
        }

        $validatedData = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $user->id,
            'office_id' => 'sometimes|nullable|exists:offices,id',
        ]);

        try {
            // Update user data
            $user->fill($validatedData);
            $user->save();

            // Get office name if office_id is set
            $officeName = null;
            if ($user->office_id) {
                $office = Office::find($user->office_id);
                $officeName = $office ? $office->name : null;
            }

            Log::info('User profile updated', ['user_id' => $user->id, 'updates' => $validatedData]);

            return response()->json([
                'message' => 'Profile updated successfully',
                'user' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'profile_picture' => $user->profile_picture,
                    'office_id' => $user->office_id,
                    'office_name' => $officeName,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update user profile', ['user_id' => $user->id, 'error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to update profile: ' . $e->getMessage()], 500);
        }
    }
}
