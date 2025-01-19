<?php

// DeviceController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Device;
use App\Models\Office;

class DeviceController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'description' => 'required|string',
            'office_id' => 'required|exists:offices,id',
        ]);

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

    public function destroy($id)
    {
        $device = Device::find($id);
        $device->delete();
        return response()->json(['message' => 'Device deleted successfully']);
    }
}