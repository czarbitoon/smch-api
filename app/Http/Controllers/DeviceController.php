<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Device;
use App\Models\Issue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class DeviceController extends Controller
{
    public function showDevices(Request $request)
    {
        try {
            // Check database connection
            if (!DB::connection()->getPdo()) {
                Log::error('Database connection failed');
                return response()->json(['message' => 'Database connection error'], 500);
            }

            $query = Device::with(['category', 'type', 'subcategory', 'office']);

            // Filter by status if provided
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // Filter by office if provided
            if ($request->has('office_id')) {
                $query->where('office_id', $request->office_id);
            }

            $perPage = $request->input('per_page', 10); // Default to 10 items per page
            $devices = $query->paginate($perPage);
            
            Log::info('Devices fetched successfully', ['count' => $devices->total()]);
            
            return response()->json($devices)
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                ->header('Access-Control-Allow-Credentials', 'true');

        } catch (\PDOException $e) {
            Log::error('Database error in showDevices: ' . $e->getMessage());
            return response()->json(['message' => 'Database connection error'], 500);
        } catch (\Exception $e) {
            Log::error('Error in showDevices: ' . $e->getMessage());
            return response()->json(['message' => 'Internal server error'], 500);
        }
    }

    public function getDeviceStatus($id)
    {
        try {
            $device = Device::findOrFail($id);
            return response()->json(['status' => $device->status]);
        } catch (\Exception $e) {
            \Log::error('Error fetching device status: ' . $e->getMessage());
            return response()->json(['message' => 'Error fetching device status: ' . $e->getMessage()], 500);
        }
    }

    public function createDevice(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string',
                'description' => 'required|string',
                'office_id' => 'required|exists:offices,id',
                'device_category_id' => 'required|exists:device_categories,id',
                'device_type_id' => 'required|exists:device_types,id',
                'device_subcategory_id' => 'required|exists:device_subcategories,id'
            ]);

            $device = new Device($request->all());
            $device->status = 'active'; // Set default status
            $device->save();

            return response()->json([
                'message' => 'Device created successfully',
                'device' => $device->load(['category', 'type', 'subcategory', 'office'])
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error creating device: ' . $e->getMessage()], 500);
        }
    }

    public function updateDevice(Request $request, $id)
    {
        try {
            $device = Device::findOrFail($id);
            $validatedData = $request->validate([
                'name' => 'sometimes|string',
                'description' => 'sometimes|string',
                'office_id' => 'sometimes|exists:offices,id',
                'device_category_id' => 'sometimes|exists:device_categories,id',
                'device_type_id' => 'sometimes|exists:device_types,id',
                'device_subcategory_id' => 'sometimes|exists:device_subcategories,id',
                'status' => 'sometimes|string'
            ]);
            
            $device->update($validatedData);
            return response()->json([
                'message' => 'Device updated successfully',
                'device' => $device->load(['category', 'type', 'subcategory', 'office'])
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error updating device: ' . $e->getMessage()], 500);
        }
    }

    public function deleteDevice($id)
    {
        try {
            $device = Device::findOrFail($id);
            $device->delete();
            return response()->json(['message' => 'Device deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error deleting device: ' . $e->getMessage()], 500);
        }
    }

    public function logIssue(Request $request, $deviceId)
    {
        try {
            $request->validate([
                'description' => 'required|string',
            ]);

            $issue = new Issue([
                'device_id' => $deviceId,
                'description' => $request->description
            ]);
            $issue->save();

            return response()->json(['message' => 'Issue logged successfully', 'issue' => $issue], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error logging issue: ' . $e->getMessage()], 500);
        }
    }


}
