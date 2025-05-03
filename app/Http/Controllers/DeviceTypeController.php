<?php

namespace App\Http\Controllers;

use App\Models\DeviceType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DeviceTypeController extends Controller
{
    /**
     * Display a listing of the device types.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $deviceTypes = DeviceType::all();
        return response()->json($deviceTypes);
    }
}
