<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Office;
use App\Models\Device;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class OfficeController extends Controller
{
    public function addOffice(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
        ]);

        $office = new Office();
        $office->name = $request->input('name');
        $office->save();
        return response()->json(['message' => 'Office added successfully', 'office' => $office], 201);
    }

    public function getOffices()
    {
        $offices = Office::all();
        return response()->json($offices);
    }

    public function updateOffice(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string',
        ]);

        $office = Office::findOrFail($id);
        $office->name = $request->input('name');
        $office->save();
        return response()->json(['message' => 'Office updated successfully', 'office' => $office]);
    }

    public function deleteOffice($id)
    {
        $office = Office::findOrFail($id);
        $office->delete();
        return response()->json(null, 204);
    }

    public function getDevices(Request $request)
    {
        $user = Auth::user();

        // For admin users, return all devices with office information
        if ($user->role === 'admin') {
            $devices = Device::with('office')->get();
        }
        // For regular users, return only devices in their assigned office
        else {
            $devices = Device::where('office_id', $user->office_id)
                            ->with('office')
                            ->get();
        }

        return response()->json($devices);
    }
}
