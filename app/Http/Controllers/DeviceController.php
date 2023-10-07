<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Office;
use Illuminate\Http\Request;

class DeviceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Device::with('office')->get();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'office_id' => 'required|exists:offices,id'
        ]);

        $device = Device::create($validatedData);

        return response()->json([
            'message' => 'Device created successfully',
            'device' => $device
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Device  $device
     * @return \Illuminate\Http\Response
     */
    public function show(Device $device)
    {
        return $device->load('office');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Device  $device
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Device $device)
    {
        $validatedData = $request->validate([
            'name' => 'required',
            'type' => 'required',
            'office_id' => 'required|exists:offices,id'
        ]);

        $device->update($validatedData);

        return response()->json([
            'message' => 'Device updated successfully',
            'device' => $device
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Device  $device
     * @return \Illuminate\Http\Response
     */
    public function destroy(Device $device)
    {
        $device->delete();
        return response()->json([
            'message' => 'Device deleted successfully'
        ]);
    }

    /**
     * Get all devices for the specified office ID (including soft-deleted devices).
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function getDevices($id)
    {
        $office = Office::withTrashed()->findOrFail($id);

        $devices = $office->devices()->withTrashed()->get();

        return response()->json($devices);
    }

    public function show($id)
{
    // Retrieve device information by $id and pass it to the view
    return view('device.show', ['device' => Device::find($id)]);
}
}
