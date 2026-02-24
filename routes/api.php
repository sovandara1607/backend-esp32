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

// ─── ESP32 Fan Control (per-device) ─────────────────────────
Route::get('/fan/status', function (Request $request) {
    $identifier = $request->query('device', 'ESP32-FAN-001');
    $source = $request->query('source', 'esp32');

    $device = \App\Models\Device::where('device_identifier', $identifier)->first();

    // Only mark device online when the request comes from the ESP32 hardware
    if ($device && $source === 'esp32') {
        $device->update(['status' => 'online', 'last_seen_at' => now()]);
    }

    $deviceId = $device?->id;

    return response()->json([
        'status' => Cache::get("fan_state_{$deviceId}", 'off'),
        'speed' => (int) Cache::get("fan_speed_{$deviceId}", 255),
        'pin_ena' => $device?->configuration['pin_ena'] ?? 15,
        'pin_in2' => $device?->configuration['pin_in2'] ?? 2,
    ]);
});

Route::get('/fan/on', function (Request $request) {
    $identifier = $request->query('device', 'ESP32-FAN-001');
    $device = \App\Models\Device::where('device_identifier', $identifier)->first();
    if (!$device) {
        return response()->json(['error' => 'Device not found'], 404);
    }

    Cache::forever("fan_state_{$device->id}", 'on');

    if (Cache::get("temp_control_active_{$device->id}")) {
        Cache::forget("temp_control_active_{$device->id}");
        Cache::forget("temp_control_profile_id_{$device->id}");
        \App\Models\TemperatureProfile::where('is_active', true)->update(['is_active' => false]);
    }

    $device->commands()->create([
        'user_id' => $device->user_id,
        'command' => 'on',
        'status' => 'executed',
        'executed_at' => now(),
    ]);

    return response()->json(['message' => 'Command Sent: ON', 'status' => 'on']);
});

Route::get('/fan/off', function (Request $request) {
    $identifier = $request->query('device', 'ESP32-FAN-001');
    $device = \App\Models\Device::where('device_identifier', $identifier)->first();
    if (!$device) {
        return response()->json(['error' => 'Device not found'], 404);
    }

    Cache::forever("fan_state_{$device->id}", 'off');

    if (Cache::get("temp_control_active_{$device->id}")) {
        Cache::forget("temp_control_active_{$device->id}");
        Cache::forget("temp_control_profile_id_{$device->id}");
        \App\Models\TemperatureProfile::where('is_active', true)->update(['is_active' => false]);
    }

    $device->commands()->create([
        'user_id' => $device->user_id,
        'command' => 'off',
        'status' => 'executed',
        'executed_at' => now(),
    ]);

    return response()->json(['message' => 'Command Sent: OFF', 'status' => 'off']);
});

Route::get('/fan/speed/{value}', function (Request $request, $value) {
    $identifier = $request->query('device', 'ESP32-FAN-001');
    $device = \App\Models\Device::where('device_identifier', $identifier)->first();
    if (!$device) {
        return response()->json(['error' => 'Device not found'], 404);
    }

    $speed = max(0, min(255, (int) $value));
    Cache::forever("fan_speed_{$device->id}", $speed);

    if (Cache::get("temp_control_active_{$device->id}")) {
        Cache::forget("temp_control_active_{$device->id}");
        Cache::forget("temp_control_profile_id_{$device->id}");
        \App\Models\TemperatureProfile::where('is_active', true)->update(['is_active' => false]);
    }

    $device->commands()->create([
        'user_id' => $device->user_id,
        'command' => 'set_speed',
        'payload' => ['speed' => $speed],
        'status' => 'executed',
        'executed_at' => now(),
    ]);

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
