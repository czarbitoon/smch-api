<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Device;
use App\Models\Issue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Filters\DeviceFilter;

class DeviceController extends Controller
{
    private function normalizeRequestTypes(Request $request)
    {
        Log::debug('Normalizing request types', [
            'raw_office_id' => $request->input('office_id'),
            'raw_page' => $request->input('page'),
            'raw_per_page' => $request->input('per_page')
        ]);

        $normalized = [
            'office_id' => $this->safeInt($request->input('office_id')),
            'page' => $this->safeInt($request->input('page', 1)),
            'per_page' => $this->safeInt($request->input('per_page', 10))
        ];

        Log::debug('Normalized request types', $normalized);
        return $normalized;
    }

    private function safeInt($value): ?int
    {
        // Handle numeric strings and integers
        if (is_numeric($value)) {
            $result = (int)$value;
            Log::debug('Safe integer conversion result', ['input' => $value, 'result' => $result]);
            return $result;
        }
        Log::debug('Non-numeric value provided', ['input' => $value]);
        return null;
    }

    public function showDevices(Request $request)
    {
        try {
            Log::info('Starting showDevices request', [
                'request_data' => $request->all(),
                'user_id' => auth()->id() ?? 'unauthenticated'
            ]);

            // Check database connection
            try {
                DB::connection()->getPdo();
                Log::info('Database connection successful');
            } catch (\Exception $e) {
                Log::error('Database connection failed', ['error' => $e->getMessage()]);
                return response()->json(['success' => false, 'message' => 'Database connection error', 'error' => $e->getMessage()], 500);
            }

            $query = Device::with(['category', 'type', 'office']);

            // Integrate DeviceFilter for dynamic filtering
            $filters = $request->only([
                'device_category_id',
                'device_type_id',
                'office_id',
                'status',
                'manufacturer',
                'serial_number',
                'model_number',
                'name'
            ]);
            $deviceFilter = new DeviceFilter($filters);
            $query = $deviceFilter->apply($query);

            // Pagination
            $perPage = $request->input('per_page', 10);
            $devices = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $devices
            ]);
        } catch (\Exception $e) {
            Log::error('Error in showDevices', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Error fetching devices', 'error' => $e->getMessage()], 500);
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
