<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Device;
use App\Models\Issue; // Importing the Issue model

class DeviceController extends Controller
{
    // Existing methods...

    public function logIssue(Request $request)
    {
        $request->validate([
            'device_id' => 'required|exists:devices,id',
            'issue_description' => 'required|string',
        ]);

        // Logic to log the issue
        $issue = new Issue();
        $issue->device_id = $request->input('device_id');
        $issue->description = $request->input('issue_description');
        $issue->save();
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
