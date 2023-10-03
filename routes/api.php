<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\OfficeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);
Route::post('/addOffice', [OfficeController::class, 'addOffice']);
Route::post('/createDevice', [DeviceController::class, 'store']);
Route::post('/showDevice', [DeviceController::class, 'getDevices']);
Route::post('/updateDevice', [DeviceController::class, 'update']);
Route::post('/deleteDevice', [DeviceController::class, 'destroy']);

Route::get('/protected', function () {
    return response()->json(['message' => 'This route requires authentication']);
})->middleware('auth.token');


Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::post('/admin', [AuthController::class, 'adminAccess']);
});

Route::middleware(['auth:sanctum', 'role:user'])->group(function () {
    Route::post('/user', [AuthController::class, 'userAccess']);
});

Route::middleware(['auth:sanctum', 'role:staff'])->group(function () {
    Route::post('/staff', [AuthController::class, 'staffAccess']);
});
