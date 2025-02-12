<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Device;
use App\Models\Issue; // Importing the Issue model

class DeviceController extends Controller
{
    public function showDevices()
    {
        $devices = Device::all(); // Fetch all devices
        return response()->json($devices);
    }

    public function createDevice(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'status' => 'required|string',
        ]);

        $device = new Device();
        $device->name = $request->input('name');
        $device->status = $request->input('status');
        $device->save();
        return response()->json(['message' => 'Device created successfully', 'device' => $device], 201);
    }

    public function updateDevice(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string',
            'status' => 'required|string',
        ]);

        $device = Device::findOrFail($id);
        $device->name = $request->input('name');
        $device->status = $request->input('status');
        $device->save();
        return response()->json(['message' => 'Device updated successfully', 'device' => $device]);
    }

    public function deleteDevice($id)
    {
        $device = Device::findOrFail($id);
        $device->delete();
        return response()->json(null, 204);
    }

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
