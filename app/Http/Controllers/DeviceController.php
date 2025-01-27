<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Device;

class DeviceController extends Controller
{
    // Existing methods...

    public function logIssue(Request $request)
    {
        $request->validate([
            'device_id' => 'required|exists:devices,id',
            'issue_description' => 'required|string',
        ]);

        // Logic to log the issue (e.g., save to a database or send a notification)
        // This is a placeholder for the actual implementation.
        return response()->json(['message' => 'Issue logged successfully']);
    }

    public function getDeviceStatus($id)
    {
        $device = Device::find($id);
        if (!$device) {
            return response()->json(['message' => 'Device not found'], 404);
        }

        return response()->json(['status' => $device->status]);
    }
}
