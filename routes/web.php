<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

Route::get('/', function () {
    return response()->json([
        'message' => 'SMCH API is running',
        'status' => 'ok',
        'timestamp' => now(),
        'version' => '1.0.0'
    ]);
});

// Health check route (also available on web routes)
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
        'debug' => config('app.debug'),
        'app_key_set' => !empty(config('app.key'))
    ]);
});
