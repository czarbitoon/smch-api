<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Office;
use App\Models\Device;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OfficeController extends Controller
{
    public function index()
    {
        try {
            Log::info('Fetching all offices');

            // Check database connection
            if (!DB::connection()->getPdo()) {
                Log::error('Database connection failed');
                return response()->json(['success' => false, 'message' => 'Database connection error'], 500);
            }

            $query = Office::query();
            $offices = $query->get();
            $formattedOffices = [];

            foreach ($offices as $office) {
                $formattedOffices[] = [
                    'id' => (int)$office->id,
                    'name' => $office->name,
                    'description' => $office->description ?? '',
                    'devices_count' => (int)($office->devices_count ?? 0),
                    'created_at' => $office->created_at,
                    'updated_at' => $office->updated_at
                ];
            }

            Log::info('Offices fetched successfully', [
                'count' => $offices->count(),
                'connection' => DB::connection()->getName()
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'offices' => $formattedOffices,
                    'pagination' => [
                        'total' => (int)Office::count(),
                        'current_page' => 1,
                        'last_page' => 1
                    ]
                ]
            ]);
        } catch (\PDOException $e) {
            Log::error('Database error in index: ' . $e->getMessage(), [
                'error_code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return response()->json(['message' => 'Database connection error'], 500);
        } catch (\Exception $e) {
            Log::error('Error fetching offices: ' . $e->getMessage(), [
                'error_code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return response()->json(['message' => 'Error fetching offices: ' . $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            Log::info('Creating new office with data:', $request->all());

            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'location' => 'required|string|max:255'
            ]);

            $office = Office::create($validatedData);
            Log::info('Office created successfully', ['office_id' => $office->id]);

            return response()->json([
                'message' => 'Office created successfully',
                'office' => $office
            ], 201);
        } catch (ValidationException $e) {
            Log::warning('Validation error while creating office:', [
                'errors' => $e->errors(),
                'data' => $request->all()
            ]);
            return response()->json(['message' => 'Validation error', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error creating office: ' . $e->getMessage(), [
                'data' => $request->all(),
                'error_code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return response()->json(['message' => 'Error creating office'], 500);
        }
    }

    public function show($id)
    {
        try {
            Log::info('Fetching office details', ['office_id' => $id]);
            $office = Office::with('devices')->findOrFail($id);
            Log::info('Office details retrieved successfully', [
                'office_id' => $id,
                'device_count' => $office->devices->count()
            ]);
            return response()->json($office);
        } catch (\Exception $e) {
            Log::error('Error fetching office details: ' . $e->getMessage(), [
                'office_id' => $id,
                'error_code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return response()->json(['message' => 'Error fetching office details'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            Log::info('Updating office', ['office_id' => $id, 'data' => $request->all()]);

            $office = Office::findOrFail($id);
            $validatedData = $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'location' => 'sometimes|required|string|max:255'
            ]);

            $office->update($validatedData);
            Log::info('Office updated successfully', ['office_id' => $id, 'changes' => $validatedData]);

            return response()->json([
                'message' => 'Office updated successfully',
                'office' => $office
            ]);
        } catch (ValidationException $e) {
            Log::warning('Validation error while updating office:', [
                'office_id' => $id,
                'errors' => $e->errors(),
                'data' => $request->all()
            ]);
            return response()->json(['message' => 'Validation error', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error updating office: ' . $e->getMessage(), [
                'office_id' => $id,
                'data' => $request->all(),
                'error_code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return response()->json(['message' => 'Error updating office'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            Log::info('Attempting to delete office', ['office_id' => $id]);

            $office = Office::findOrFail($id);
            // Check if office has any devices
            $deviceCount = Device::where('office_id', $id)->count();

            if ($deviceCount > 0) {
                Log::warning('Cannot delete office with associated devices', [
                    'office_id' => $id,
                    'device_count' => $deviceCount
                ]);
                return response()->json([
                    'message' => 'Cannot delete office with associated devices'
                ], 400);
            }

            $office->delete();
            Log::info('Office deleted successfully', ['office_id' => $id]);

            return response()->json(['message' => 'Office deleted successfully']);
        } catch (\Exception $e) {
            Log::error('Error deleting office: ' . $e->getMessage(), [
                'office_id' => $id,
                'error_code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return response()->json(['message' => 'Error deleting office'], 500);
        }
    }
}
