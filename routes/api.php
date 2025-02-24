<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OfficeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TokenController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\DeviceCategoryController;
use App\Http\Controllers\UserController;

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

// Admin routes
Route::get('/admin/stats', [AdminController::class, 'stats'])->middleware('auth:sanctum');

// User routes
Route::get('/user/stats', [UserController::class, 'stats'])->middleware('auth:sanctum');

// Device routes
Route::get('/devices', [DeviceController::class, 'showDevices']);
Route::get('/office-devices', [OfficeController::class, 'getDevices'])->middleware('auth:sanctum');
Route::post('/devices', [DeviceController::class, 'createDevice']);
Route::put('/devices/{id}', [DeviceController::class, 'updateDevice']);
Route::delete('/devices/{id}', [DeviceController::class, 'deleteDevice']);
Route::get('/devices/{id}/status', [DeviceController::class, 'getDeviceStatus']);

// Report routes
Route::get('/reports', [ReportController::class, 'getReports']);
Route::post('/reports', [ReportController::class, 'addReport']);
Route::put('/reports/{id}', [ReportController::class, 'update']);
Route::delete('/reports/{id}', [ReportController::class, 'delete']);

// Device Category routes
Route::get('/device-categories', [DeviceCategoryController::class, 'getCategories']);
Route::get('/device-categories/{categoryId}/types', [DeviceCategoryController::class, 'getTypes']);
Route::get('/device-types/{typeId}/subcategories', [DeviceCategoryController::class, 'getSubcategories']);
Route::post('/device-categories', [DeviceCategoryController::class, 'createCategory']);
Route::post('/device-categories/{categoryId}/types', [DeviceCategoryController::class, 'createType']);
Route::post('/device-types/{typeId}/subcategories', [DeviceCategoryController::class, 'createSubcategory']);
