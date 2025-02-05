<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\OfficeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TokenController;

// CSRF Token route
Route::get('/sanctum/csrf-cookie', function () {
    return response()->json(['message' => 'CSRF token fetched']);
});

// Auth routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);
Route::get('/profile', [AuthController::class, 'profile'])->middleware('auth:sanctum');

// Device routes
Route::post('/devices/issues', [DeviceController::class, 'logIssue'])->middleware('auth:sanctum');
Route::get('/devices/{id}/status', [DeviceController::class, 'getDeviceStatus'])->middleware('auth:sanctum');

// Office routes
Route::post('/offices', [OfficeController::class, 'addOffice'])->middleware('auth:sanctum');
Route::get('/offices', [OfficeController::class, 'getOffices'])->middleware('auth:sanctum');

// Profile routes
Route::get('/profile', [ProfileController::class, 'show'])->middleware('auth:sanctum');
Route::post('/profile/picture', [ProfileController::class, 'uploadPicture'])->middleware('auth:sanctum');

// Report routes
Route::post('/reports', [ReportController::class, 'addReport'])->middleware('auth:sanctum');
Route::get('/reports', [ReportController::class, 'getReports'])->middleware('auth:sanctum');
Route::put('/reports/{id}', [ReportController::class, 'update'])->middleware('auth:sanctum');
Route::delete('/reports/{id}', [ReportController::class, 'delete'])->middleware('auth:sanctum');
Route::post('/reports/{id}/resolve', [ReportController::class, 'resolve'])->middleware('auth:sanctum');

// Token routes
Route::post('/tokens', [TokenController::class, 'createToken']);
