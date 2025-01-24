<?php

// api.php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\OfficeController;
use App\Http\Controllers\ReportsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);

Route::post('/addOffice', [OfficeController::class, 'addOffice']);
Route::post('/createDevice', [DeviceController::class, 'store']);
Route::post('/showDevice', [DeviceController::class, 'getDevices']);
Route::post('/updateDevice', [DeviceController::class, 'update']);
Route::post('/deleteDevice', [DeviceController::class, 'destroy']);
// routes/api.php

Route::middleware('auth:sanctum', 'token_lifetimes:personal_access=24 hours')->get('/api/profile', [ProfileController::class, 'show']);
Route::middleware('auth:sanctum')->group(function () {
    
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::post('/profile/upload-picture', [ProfileController::class, 'uploadPicture']);
    Route::post('/logout', function (Request $request) {
        $request->user()->currentAccessToken()->delete();
        return response()->noContent();
    });

    Route::get('/protected', function () {
        return response()->json(['message' => 'This route requires authentication']);
    });

    Route::middleware('role:admin')->group(function () {
        Route::post('/admin', [AuthController::class, 'adminAccess']);
    });

    Route::middleware('role:user')->group(function () {
        Route::post('/user', [AuthController::class, 'userAccess']);
    });

    Route::middleware('role:staff')->group(function () {
        Route::post('/staff', [AuthController::class, 'staffAccess']);
    });
});

Route::prefix('reports')->group(function () {
    Route::post('/', [ReportsController::class, 'store']);
    Route::get('/', [ReportsController::class, 'index']);
    Route::get('/{id}', [ReportsController::class, 'show']);
    Route::put('/{id}', [ReportsController::class, 'update']);
    Route::delete('/{id}', [ReportsController::class, 'destroy']);
});