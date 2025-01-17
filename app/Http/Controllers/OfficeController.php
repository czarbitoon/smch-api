<?php

// OfficeController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Office;

class OfficeController extends Controller
{
    public function addOffice(Request $request)
    {
        $office = new Office();
        $office->name = $request->input('name');
        $office->address = $request->input('address');
        $office->save();
        return response()->json(['message' => 'Office added successfully']);
    }
}