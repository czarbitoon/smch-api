<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OfficeController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('dashboard');
})->name('dashboard');

Route::get('/devices', [DeviceController::class, 'index'])->name('devices');
Route::get('/devices/create', [DeviceController::class, 'create'])->name('device.create');
Route::post('/devices', [DeviceController::class, 'store'])->name('device.store');
Route::get('/devices/{id}/edit', [DeviceController::class, 'edit'])->name('device.edit');
Route::put('/devices/{id}', [DeviceController::class, 'update'])->name('device.update');
Route::delete('/devices/{id}', [DeviceController::class, 'destroy'])->name('device.destroy');
Route::get('/devices/{id}/delete', [DeviceController::class, 'delete'])->name('device.delete');
Route::get('/devices/{id}/restore', [DeviceController::class, 'restore'])->name('device.restore');

Route::get('/devices/{id}', [DeviceController::class, 'show'])->name('device.show');
Route::get('/report/create/{device_id}', [ReportController::class, 'create'])->name('report.create');
Route::get('/reports', [ReportController::class, 'index'])->name('reports');

Route::get('/profile', [AuthController::class, 'profile'])->name('profile')->middleware('auth:api');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');



