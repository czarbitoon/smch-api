<?php

// api.php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\OfficeController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/token/refresh', [AuthController::class, 'refreshToken']);

    Route::post('/addOffice', [OfficeController::class, 'addOffice']);
    Route::post('/createDevice', [DeviceController::class, 'store']);
    Route::post('/showDevice', [DeviceController::class, 'getDevices']);
    Route::post('/updateDevice', [DeviceController::class, 'update']);
    Route::post('/deleteDevice', [DeviceController::class, 'destroy']);

    Route::get('/profile', [ProfileController::class, 'show']);
    Route::post('/profile/upload-picture', [ProfileController::class, 'uploadPicture']);

    Route::middleware('role:admin')->group(function () {
        Route::post('/admin', [AuthController::class, 'adminAccess']);
    });

    Route::middleware('role:user')->group(function () {
        Route::post('/user', [AuthController::class, 'userAccess']);
    });

    Route::middleware('role:staff')->group(function () {
        Route::post('/staff', [AuthController::class, 'staffAccess']);
    });

    Route::prefix('reports')->group(function () {
        Route::post('/', [ReportsController::class, 'store']);
        Route::get('/', [ReportsController::class, 'index']);
        Route::get('/{id}', [ReportsController::class, 'show']);
        Route::put('/{id}', [ReportsController::class, 'update']);
        Route::delete('/{id}', [ReportsController::class, 'destroy']);
    });
});