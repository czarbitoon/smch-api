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
use App\Http\Controllers\ProfileController;

// CSRF Token route
Route::get('/sanctum/csrf-cookie', function () {
    return response()->json(['message' => 'CSRF token fetched']);
});

Route::post('/token', [TokenController::class, 'createToken']); // Route for creating a token

// Unprotected routes for offices
Route::get('/offices', [OfficeController::class, 'index']);
Route::get('/offices/{id}', [OfficeController::class, 'show']);
Route::post('/offices', [OfficeController::class, 'store']);
Route::put('/offices/{id}', [OfficeController::class, 'update']);
Route::delete('/offices/{id}', [OfficeController::class, 'destroy']);

// Auth routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::get('/profile', [AuthController::class, 'profile'])->middleware('auth:sanctum');

// Profile routes
Route::post('/profile/update', [ProfileController::class, 'update'])->middleware('auth:sanctum');
Route::post('/profile/upload-picture', [ProfileController::class, 'uploadPicture'])->middleware('auth:sanctum');


// Admin routes
Route::get('/admin/stats', [AdminController::class, 'stats'])->middleware('auth:sanctum');

// User routes
Route::get('/user/stats', [UserController::class, 'stats'])->middleware('auth:sanctum');

// Device routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/devices', [DeviceController::class, 'showDevices']);
    Route::get('/office-devices', [OfficeController::class, 'getDevices']);
    Route::post('/devices', [DeviceController::class, 'createDevice']);
    Route::put('/devices/{id}', [DeviceController::class, 'updateDevice']);
    Route::delete('/devices/{id}', [DeviceController::class, 'deleteDevice']);
    Route::get('/devices/{id}/status', [DeviceController::class, 'getDeviceStatus']);
});

// Report routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/reports', [ReportController::class, 'getReports']);
    Route::post('/reports', [ReportController::class, 'addReport']);
    Route::put('/reports/{id}', [ReportController::class, 'update']);
    Route::delete('/reports/{id}', [ReportController::class, 'delete']);
    Route::post('/reports/{id}/resolve', [ReportController::class, 'resolveReport']);
});

// Device Category routes
Route::get('/device-categories', [DeviceCategoryController::class, 'getCategories']);
Route::get('/device-categories/{categoryId}/types', [DeviceCategoryController::class, 'getTypes']);
Route::get('/device-categories/{categoryId}/subcategories', [DeviceCategoryController::class, 'getSubcategoriesByCategory']);

Route::get('/device-types/{typeId}/subcategories', [DeviceCategoryController::class, 'getSubcategories']);
Route::post('/device-categories', [DeviceCategoryController::class, 'createCategory']);
Route::post('/device-categories/{categoryId}/types', [DeviceCategoryController::class, 'createType']);
Route::post('/device-types/{typeId}/subcategories', [DeviceCategoryController::class, 'createSubcategory']);

// Notification routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/notifications', [NotificationsController::class, 'index']);
    Route::post('/notifications/{id}/read', [NotificationsController::class, 'markAsRead']);
    Route::post('/notifications', [NotificationsController::class, 'store']);
});

// Password reset/change endpoints
Route::post('/password/forgot', [AuthController::class, 'forgotPassword']);
Route::post('/password/reset', [AuthController::class, 'resetPassword']);
Route::post('/password/change', [AuthController::class, 'changePassword'])->middleware('auth:sanctum');

// User management endpoints (admin only)
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/{id}', [UserController::class, 'show']);
    Route::put('/users/{id}', [UserController::class, 'update']);
    Route::put('/users/{id}/role', [UserController::class, 'changeRole']);
    Route::put('/users/{id}/deactivate', [UserController::class, 'deactivate']);
});
