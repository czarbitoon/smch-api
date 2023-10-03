<?php

namespace App\Http\Controllers;
use App\Models\Office;
use Illuminate\Http\Request;

// This controller is used to add a new office to the database
class OfficeController extends Controller
{
    // This method is used to add a new office to the database
    public function addOffice(Request $request)
{
    try {
        // Validate the request
        $this->validate($request, [
            'name' =>'required|string|max:255',
            // add any other fields you need to validate here
        ]);

        // Create a new office object
        $office = new Office;
        // Set the name of the office
        $office->name = $request->input('name');
        // add any other fields you need to set here
        $office->save();

        // Return a message indicating the office was added successfully
        return response()->json(['message' => 'Office added successfully'], 201);

    } catch (ValidationException $e) {
        // Return an error message if there is a validation error
        return response()->json(['error' => $e->getMessage()], 400);
    }
}

}
