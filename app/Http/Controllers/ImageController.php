<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class ImageController extends Controller
{
    // Upload image
    public function upload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:5120', // 5MB max
            'folder' => 'nullable|string',
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }
        $folder = $request->input('folder', 'uploads');
        $file = $request->file('image');
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs($folder, $filename, 'public');
        return response()->json([
            'message' => 'Image uploaded successfully',
            'path' => $path,
            'url' => Storage::disk('public')->url($path),
        ], 201);
    }

    // Serve image from storage
    public function show($folder, $filename)
    {
        $path = $folder . '/' . $filename;
        if (!Storage::disk('public')->exists($path)) {
            return response()->json(['message' => 'Image not found'], 404);
        }
        $file = Storage::disk('public')->get($path);
        $mime = Storage::disk('public')->mimeType($path);
        return response($file, 200)->header('Content-Type', $mime);
    }

    // Delete image
    public function destroy(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'path' => 'required|string',
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }
        $path = $request->input('path');
        if (!Storage::disk('public')->exists($path)) {
            return response()->json(['message' => 'Image not found'], 404);
        }
        Storage::disk('public')->delete($path);
        return response()->json(['message' => 'Image deleted successfully']);
    }
}
