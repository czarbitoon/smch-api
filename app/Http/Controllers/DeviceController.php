<?php

// DeviceController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Device;

class DeviceController extends Controller
{
    public function addDevice(Request $request)
    {
        $device = new Device();
        $device->name = $request->input('name');
        $device->description = $request->input('description');
        $device->office_id = $request->input('office_id');
        $device->save();
        return response()->json(['message' => 'Device added successfully']);
    }

    public function getDevices()
    {
        $devices = Device::all();
        return response()->json($devices);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string',
            'description' => 'required|string',
        ]);

        $device = Device::find($id);
        $device->name = $request->input('name');
        $device->description = $request->input('description');
        $device->save();
        return response()->json(['message' => 'Device updated successfully']);
    }

    public function delete($id)
    {
        $device = Device::find($id);
        $device->delete();
        return response()->json(['message' => 'Device deleted successfully']);
    }

    public function resolve($id)
    {
        $device = Device::find($id);
        $device->status = 'resolved';
        $device->save();
        return response()->json(['message' => 'Device resolved successfully']);
    }
}