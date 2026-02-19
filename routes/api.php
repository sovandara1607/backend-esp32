<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


// 1. ESP32 checks this URL
Route::get('/fan/status', function () {
    return response()->json([
        'status' => Cache::get('fan_state', 'off')
    ]);
});

// 2. You visit this to turn it ON
Route::get('/fan/on', function () {
    Cache::forever('fan_state', 'on');
    return "Command Sent: ON";
});

// 3. You visit this to turn it OFF
Route::get('/fan/off', function () {
    Cache::forever('fan_state', 'off');
    return "Command Sent: OFF";
});
