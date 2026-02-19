<?php

use App\Http\Controllers\FanController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// ESP32 polls this to get current mode, speed, and status
Route::get('/fan/status', [FanController::class, 'getStatus']);

// Web dashboard polls this for complete state (includes temperature)
Route::get('/fan/state', [FanController::class, 'getFullState']);

// Set fan speed 0-100 (used by manual slider and voice commands)
Route::post('/fan/speed', [FanController::class, 'setSpeed']);

// Switch control mode (manual, voice, temperature)
Route::post('/fan/mode', [FanController::class, 'setMode']);

// ESP32 reports current temperature reading
Route::post('/fan/temperature', [FanController::class, 'reportTemperature']);
