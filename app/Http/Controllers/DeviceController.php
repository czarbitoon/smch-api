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
            Log::info('Fetching devices with parameters:', [
                'status' => $request->status,
                'office_id' => $request->office_id,
                'per_page' => $request->input('per_page', 10)
            ]);

            // Check database connection
            if (!DB::connection()->getPdo()) {
                Log::error('Database connection failed');
                return response()->json(['success' => false, 'message' => 'Database connection error'], 500);
            }

            $query = Device::with(['category', 'type', 'subcategory', 'office']);

            // Filter by status if provided
            if ($request->has('status')) {
                Log::info('Filtering devices by status:', ['status' => $request->status]);
                $query->where('status', $request->status);
            }

            // Filter by office if provided
            if ($request->has('office_id')) {
                Log::info('Filtering devices by office:', ['office_id' => $request->office_id]);
                $query->where('office_id', (int)$request->office_id);
            }

            $perPage = $request->input('per_page', 10); // Default to 10 items per page
            $devices = $query->paginate($perPage);

            // Transform the data to ensure consistent integer types
            $transformedData = $devices->through(function ($device) {
                $device->id = (int)$device->id;
                $device->office_id = (int)$device->office_id;
                $device->device_category_id = (int)$device->device_category_id;
                $device->device_type_id = (int)$device->device_type_id;
                $device->device_subcategory_id = (int)$device->device_subcategory_id;
                return $device;
            });

            Log::info('Devices fetched successfully', [
                'total_count' => $devices->total(),
                'current_page' => $devices->currentPage(),
                'per_page' => $devices->perPage()
            ]);

            return response()->json([
                'success' => true,
                'data' => $transformedData
            ]);

        } catch (\PDOException $e) {
            Log::error('Database error in showDevices: ' . $e->getMessage(), [
                'error_code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return response()->json(['success' => false, 'message' => 'Database connection error'], 500);
        } catch (\Exception $e) {
            Log::error('Error in showDevices: ' . $e->getMessage(), [
                'error_code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return response()->json(['success' => false, 'message' => 'Internal server error'], 500);
        }
    }

    public function getDeviceStatus($id)
    {
        try {
            Log::info('Fetching device status', ['device_id' => $id]);
            $device = Device::findOrFail((int)$id);
            Log::info('Device status retrieved successfully', ['device_id' => $id, 'status' => $device->status]);
            return response()->json(['success' => true, 'status' => $device->status]);
        } catch (\Exception $e) {
            Log::error('Error fetching device status: ' . $e->getMessage(), [
                'device_id' => $id,
                'error_code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return response()->json(['success' => false, 'message' => 'Error fetching device status: ' . $e->getMessage()], 500);
        }
    }

    public function createDevice(Request $request)
    {
        try {
            Log::info('Creating new device with data:', $request->all());

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

            Log::info('Device created successfully', ['device_id' => $device->id]);

            return response()->json([
                'message' => 'Device created successfully',
                'device' => $device->load(['category', 'type', 'subcategory', 'office'])
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error creating device: ' . $e->getMessage(), [
                'data' => $request->all(),
                'error_code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return response()->json(['message' => 'Error creating device: ' . $e->getMessage()], 500);
        }
    }

    public function updateDevice(Request $request, $id)
    {
        try {
            Log::info('Updating device', ['device_id' => $id, 'data' => $request->all()]);

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
            Log::info('Device updated successfully', ['device_id' => $id, 'changes' => $validatedData]);

            return response()->json([
                'message' => 'Device updated successfully',
                'device' => $device->load(['category', 'type', 'subcategory', 'office'])
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating device: ' . $e->getMessage(), [
                'device_id' => $id,
                'data' => $request->all(),
                'error_code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return response()->json(['message' => 'Error updating device: ' . $e->getMessage()], 500);
        }
    }

    public function deleteDevice($id)
    {
        try {
            Log::info('Attempting to delete device', ['device_id' => $id]);
            $device = Device::findOrFail($id);
            $device->delete();
            Log::info('Device deleted successfully', ['device_id' => $id]);
            return response()->json(['message' => 'Device deleted successfully'], 200);
        } catch (\Exception $e) {
            Log::error('Error deleting device: ' . $e->getMessage(), [
                'device_id' => $id,
                'error_code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return response()->json(['message' => 'Error deleting device: ' . $e->getMessage()], 500);
        }
    }

    public function logIssue(Request $request, $deviceId)
    {
        try {
            Log::info('Logging new issue for device', ['device_id' => $deviceId, 'description' => $request->description]);

            $request->validate([
                'description' => 'required|string',
            ]);

            $issue = new Issue([
                'device_id' => $deviceId,
                'description' => $request->description
            ]);
            $issue->save();

            Log::info('Issue logged successfully', ['issue_id' => $issue->id, 'device_id' => $deviceId]);

            return response()->json(['message' => 'Issue logged successfully', 'issue' => $issue], 201);
        } catch (\Exception $e) {
            Log::error('Error logging issue: ' . $e->getMessage(), [
                'device_id' => $deviceId,
                'data' => $request->all(),
                'error_code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return response()->json(['message' => 'Error logging issue: ' . $e->getMessage()], 500);
        }
    }
}
