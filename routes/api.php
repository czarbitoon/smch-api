<?php
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\OfficeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TokenController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\DeviceCategoryController;
use App\Http\Controllers\NotificationsController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ImageController;

// Health check route
Route::get('/health', function () {
    try {
        $dbStatus = 'connected';
        DB::connection()->getPdo();
    } catch (\Exception $e) {
        $dbStatus = 'failed: ' . $e->getMessage();
    }

    return response()->json([
        'status' => 'ok',
        'database' => $dbStatus,
        'timestamp' => now(),
        'env' => config('app.env'),
        'debug' => config('app.debug')
    ]);
});

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
    Route::get('/devices/{id}', [DeviceController::class, 'showDevice']);
    Route::get('/office-devices', [OfficeController::class, 'getDevices']);
    Route::post('/devices', [DeviceController::class, 'createDevice']);
    Route::put('/devices/{id}', [DeviceController::class, 'updateDevice']);
    Route::delete('/devices/{id}', [DeviceController::class, 'deleteDevice']);
    Route::get('/devices/{id}/status', [DeviceController::class, 'getDeviceStatus']);
});

// Report routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/reports', [ReportController::class, 'getReports']);
    Route::get('/reports/{id}', [ReportController::class, 'show']);
    Route::post('/reports', [ReportController::class, 'addReport']);
    Route::put('/reports/{id}', [ReportController::class, 'update']);
    Route::delete('/reports/{id}', [ReportController::class, 'delete']);
    Route::post('/reports/{id}/resolve', [ReportController::class, 'resolveReport']);
    Route::post('/reports/{id}/status', [ReportController::class, 'updateStatus']);
});

// Device Category routes
Route::get('/device-categories', [DeviceCategoryController::class, 'getCategories']);
Route::get('/device-categories/{categoryId}/types', [DeviceCategoryController::class, 'getTypes']);
Route::post('/device-categories', [DeviceCategoryController::class, 'createCategory']);
Route::post('/device-categories/{categoryId}/types', [DeviceCategoryController::class, 'createType']);

// Device Type routes
Route::get('/device-types', [\App\Http\Controllers\DeviceTypeController::class, 'index']);
Route::get('/device-types/{id}', [\App\Http\Controllers\DeviceTypeController::class, 'show']);
Route::post('/device-types', [\App\Http\Controllers\DeviceTypeController::class, 'store']);
Route::put('/device-types/{id}', [\App\Http\Controllers\DeviceTypeController::class, 'update']);
Route::delete('/device-types/{id}', [\App\Http\Controllers\DeviceTypeController::class, 'destroy']);

// Notification routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/notifications', [NotificationsController::class, 'index']);
    Route::post('/notifications/{id}/read', [NotificationsController::class, 'markAsRead']);
    Route::post('/notifications', [NotificationsController::class, 'store']);
    Route::get('/notifications/unread-count', [NotificationsController::class, 'unreadCount']);
    // Image upload and deletion (protected)
    Route::post('/images/upload', [ImageController::class, 'upload']);
    Route::delete('/images', [ImageController::class, 'destroy']);
});
// Public image serving
Route::get('/images/{folder}/{filename}', [ImageController::class, 'show']);

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
