<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Device;
use App\Models\Issue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

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

            $query = Device::with(['category', 'type', 'subcategory', 'office']);
            Log::debug('Initial query built with relationships');

            // Get authenticated user
            $user = auth()->user();

            // Apply office filtering based on user type
            // Regular users and staff can only see devices in their office
            // Admins and super admins can see all devices
            if ($user && in_array($user->type, [0, 1])) { // Regular user or staff
                Log::info('Restricting devices to user office', [
                    'user_id' => $user->id,
                    'user_type' => $user->type,
                    'office_id' => $user->office_id
                ]);
                $query->where('office_id', $user->office_id);
            } else {
                Log::info('User is admin, showing all devices', [
                    'user_id' => $user ? $user->id : null,
                    'user_type' => $user ? $user->type : null
                ]);
            }

            // Filter by status if provided
            if ($request->has('status')) {
                Log::info('Applying status filter', ['status' => $request->status]);
                $query->where('status', $request->status);
            }

            // Filter by office if provided
            if ($request->has('office_id')) {
                $officeId = $this->safeInt($request->office_id);
                if ($officeId !== null) {
                    Log::info('Filtering devices by office:', ['office_id' => $officeId]);
                    $query->where('office_id', $officeId);
                }
            }

            $perPage = $request->input('per_page', 10);
            Log::debug('Pagination parameters', ['per_page' => $perPage]);

            $devices = $query->paginate($perPage);
            Log::info('Query executed', [
                'total_devices' => $devices->total(),
                'current_page' => $devices->currentPage(),
                'per_page' => $devices->perPage()
            ]);

            // Transform the data
            $transformedData = collect($devices->items())->map(function ($device) {
                Log::debug('Transforming device', [
                    'device_id' => $device->id,
                    'relationships_loaded' => [
                        'category' => isset($device->category),
                        'type' => isset($device->type),
                        'subcategory' => isset($device->subcategory),
                        'office' => isset($device->office)
                    ]
                ]);

                if (!isset($device->category)) {
                    Log::warning('Device category relationship not loaded', ['device_id' => $device->id]);
                }
                if (!isset($device->type)) {
                    Log::warning('Device type relationship not loaded', ['device_id' => $device->id]);
                }
                if (!isset($device->subcategory)) {
                    Log::warning('Device subcategory relationship not loaded', ['device_id' => $device->id]);
                }
                if (!isset($device->office)) {
                    Log::warning('Device office relationship not loaded', ['device_id' => $device->id]);
                }

                $transformed = [
                    'id' => (int)$device->id,
                    'name' => $device->name ?? '',
                    'description' => $device->description ?? '',
                    'device_category_id' => $device->device_category_id ? (int)$device->device_category_id : null,
                    'device_type_id' => $device->device_type_id ? (int)$device->device_type_id : null,
                    'device_subcategory_id' => $device->device_subcategory_id ? (int)$device->device_subcategory_id : null,
                    'office_id' => $device->office_id ? (int)$device->office_id : null,
                    'serial_number' => $device->serial_number ?? '',
                    'model_number' => $device->model_number ?? '',
                    'manufacturer' => $device->manufacturer ?? '',
                    'status' => $device->status ?? 'unknown',
                    'created_at' => $device->created_at ? $device->created_at->toISOString() : null,
                    'updated_at' => $device->updated_at ? $device->updated_at->toISOString() : null,
                    'category' => $device->category ? [
                        'id' => (int)$device->category->id,
                        'name' => $device->category->name ?? ''
                    ] : null,
                    'type' => $device->type ? [
                        'id' => (int)$device->type->id,
                        'name' => $device->type->name ?? ''
                    ] : null,
                    'subcategory' => $device->subcategory ? [
                        'id' => (int)$device->subcategory->id,
                        'name' => $device->subcategory->name ?? ''
                    ] : null,
                    'office' => $device->office ? [
                        'id' => (int)$device->office->id,
                        'name' => $device->office->name ?? ''
                    ] : null
                ];

                Log::debug('Device transformed successfully', ['device_id' => $device->id]);
                return $transformed;
            });

            Log::info('All devices transformed successfully', [
                'transformed_count' => $transformedData->count()
            ]);

            $response = [
                'success' => true,
                'data' => [
                    'devices' => $transformedData->values()->all() ?? [],
                    'pagination' => [
                        'total' => (int)$devices->total(),
                        'per_page' => (int)$devices->perPage(),
                        'current_page' => (int)$devices->currentPage(),
                        'last_page' => (int)$devices->lastPage(),
                        'from' => (int)$devices->firstItem() ?? 0,
                        'to' => (int)$devices->lastItem() ?? 0
                    ]
                ]
            ];

            Log::info('Sending successful response', [
                'total_devices' => count($response['data']['devices']),
                'pagination' => $response['data']['pagination']
            ]);

            return response()->json($response);

        } catch (\PDOException $e) {
            Log::error('Database error in showDevices', [
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['success' => false, 'message' => 'Database connection error'], 500);
        } catch (\Exception $e) {
            Log::error('Unexpected error in showDevices', [
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
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
