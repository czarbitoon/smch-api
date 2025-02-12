<?php

// OfficeController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Office;

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

}
