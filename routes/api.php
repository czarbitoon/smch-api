<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\OfficeController;
use App\Http\Controllers\ReportsController;
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




Route::post('/login', function (Request $request) {
    // Validate credentials
    if (! Auth::attempt($request->only('email', 'password'))) {
        // Return error message if credentials are invalid
        return response(['message' => __('auth.failed')], 422);
    }

    // Create a token for the user
    $token = auth()->user()->createToken('client-app');
    // Return the token
    return ['token' => $token->plainTextToken];
});

// Logout the user if they are logged in
Route::middleware('auth:sanctum')->post('/logout', function (Request $request) {
    // Delete the current access token
    $request->user()->currentAccessToken()->delete();
    // Return a response
    return response()->noContent();
});
// Check if the user is authenticated
Route::get('/protected', function () {
    // Return a response with a message if the user is not authenticated
    return response()->json(['message' => 'This route requires authentication']);
})->middleware('auth.token');


// Check if the user is an admin
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    // Create a route for the admin
    Route::post('/admin', [AuthController::class, 'adminAccess']);
});

// Check if the user is a user
Route::middleware(['auth:sanctum', 'role:user'])->group(function () {
    // Create a route for the user
    Route::post('/user', [AuthController::class, 'userAccess']);
});

// Check if the user is a staff
Route::middleware(['auth:sanctum', 'role:staff'])->group(function () {
    // Create a route for the staff
    Route::post('/staff', [AuthController::class, 'staffAccess']);
});



// Create a route group for the reports
Route::prefix('reports')->group(function () {
    // Create a route for the store
    Route::post('/', [ReportsController::class, 'store']);
    // Create a route for the index
    Route::get('/', [ReportsController::class, 'index']);
    // Create a route for the show
    Route::get('/{id}', [ReportsController::class, 'show']);
    // Create a route for the update
    Route::put('/{id}', [ReportsController::class, 'update']);
    // Create a route for the destroy
    Route::delete('/{id}', [ReportsController::class, 'destroy']);
});
