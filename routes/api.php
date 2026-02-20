<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\DeviceApiController;
use App\Http\Controllers\Api\SensorDataController;
use App\Http\Controllers\Api\AlertController;
use App\Http\Controllers\Api\SinricController;

// ─── API Authentication ─────────────────────────
Route::post('/register', [AuthController::class, 'apiRegister']);
Route::post('/login', [AuthController::class, 'apiLogin']);

// ─── ESP32 Fan Control (public — backward compatible) ──
Route::get('/fan/status', function () {
    return response()->json([
        'status' => Cache::get('fan_state', 'off'),
        'speed' => (int) Cache::get('fan_speed', 255),
    ]);
});

Route::get('/fan/on', function () {
    Cache::forever('fan_state', 'on');
    return response()->json(['message' => 'Command Sent: ON', 'status' => 'on']);
});

Route::get('/fan/off', function () {
    Cache::forever('fan_state', 'off');
    return response()->json(['message' => 'Command Sent: OFF', 'status' => 'off']);
});

Route::get('/fan/speed/{value}', function ($value) {
    $speed = max(0, min(255, (int) $value));
    Cache::forever('fan_speed', $speed);
    return response()->json(['message' => 'Speed set to: ' . $speed, 'speed' => $speed]);
});

// ─── ESP32 Sensor Data Ingestion (public endpoint) ─────
Route::post('/sensor-data', [SensorDataController::class, 'store']);

// ─── Sinric Pro / Google Assistant Voice Commands ──────
Route::get('/sinric/status', [SinricController::class, 'status']);
Route::post('/sinric/command', [SinricController::class, 'command']);
Route::post('/sinric/callback', [SinricController::class, 'callback']);

// ─── Device Pending Commands (ESP32 polls this) ────────
Route::get('/devices/{device}/commands/pending', [DeviceApiController::class, 'pendingCommands']);
Route::post('/devices/{device}/commands/{commandId}/ack', [DeviceApiController::class, 'acknowledgeCommand']);

// ─── Sensor Data for Charts (public for dashboard AJAX) ─
Route::get('/devices/{device}/sensor-data', [SensorDataController::class, 'index']);
Route::get('/devices/{device}/sensor-data/chart', [SensorDataController::class, 'chartData']);
Route::get('/devices/{device}/sensor-data/latest', [SensorDataController::class, 'latest']);

// ─── Authenticated API Routes ───────────────────
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/logout', [AuthController::class, 'apiLogout']);

    // Devices CRUD
    Route::apiResource('devices', DeviceApiController::class)->names([
        'index' => 'api.devices.index',
        'store' => 'api.devices.store',
        'show' => 'api.devices.show',
        'update' => 'api.devices.update',
        'destroy' => 'api.devices.destroy',
    ]);
    Route::post('/devices/{device}/commands', [DeviceApiController::class, 'sendCommand']);

    // Alerts
    Route::get('/alerts', [AlertController::class, 'index']);
    Route::post('/alerts', [AlertController::class, 'store']);
    Route::patch('/alerts/{alert}/read', [AlertController::class, 'markRead']);
    Route::post('/alerts/read-all', [AlertController::class, 'markAllRead']);
    Route::delete('/alerts/{alert}', [AlertController::class, 'destroy']);
});