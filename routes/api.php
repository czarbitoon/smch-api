<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OfficeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TokenController;

// CSRF Token route
Route::get('/sanctum/csrf-cookie', function () {
    return response()->json(['message' => 'CSRF token fetched']);
});

Route::post('/token', [TokenController::class, 'createToken']); // Route for creating a token

Route::get('/offices', [OfficeController::class, 'getOffices']);
Route::post('/offices', [OfficeController::class, 'addOffice']);
Route::put('/offices/{id}', [OfficeController::class, 'updateOffice']);
Route::delete('/offices/{id}', [OfficeController::class, 'deleteOffice']);

// Auth routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::get('/profile', [AuthController::class, 'profile'])->middleware('auth:sanctum');
Route::post('/register', [AuthController::class, 'register'])->middleware('auth:sanctum');

// Device routes
Route::get('/devices', [DeviceController::class, 'showDevices']);
Route::post('/devices', [DeviceController::class, 'createDevice']);
Route::put('/devices/{id}', [DeviceController::class, 'updateDevice']);
Route::delete('/devices/{id}', [DeviceController::class, 'deleteDevice']);

// Report routes
Route::get('/reports', [ReportController::class, 'getReports']);
Route::post('/reports', [ReportController::class, 'addReport']);
Route::put('/reports/{id}', [ReportController::class, 'update']);
Route::delete('/reports/{id}', [ReportController::class, 'delete']);
