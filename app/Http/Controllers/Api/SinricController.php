<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SinricController extends Controller
{
   /**
    * Check Sinric Pro connection status.
    * Returns whether the required env vars are configured.
    *
    * GET /api/sinric/status
    */
   public function status()
   {
      $appKey   = config('services.sinric.app_key');
      $deviceId = config('services.sinric.device_id');

      $configured = !empty($appKey) && !empty($deviceId);

      return response()->json([
         'connected'  => $configured,
         'device_id'  => $configured ? substr($deviceId, 0, 8) . '...' : null,
         'message'    => $configured
            ? 'Sinric Pro is configured and ready'
            : 'Sinric Pro credentials not configured. Add SINRIC_APP_KEY, SINRIC_APP_SECRET, and SINRIC_DEVICE_ID to your .env file.',
      ]);
   }

   /**
    * Execute a voice command (simulated Sinric Pro action).
    * These mirror the commands Google Assistant sends via Sinric Pro.
    *
    * POST /api/sinric/command
    * Body: { action: string, device: string|null, value: mixed }
    */
   public function command(Request $request)
   {
      $request->validate([
         'action' => 'required|string',
         'device' => 'nullable|string',
         'value'  => 'nullable',
      ]);

      $action = $request->input('action');
      $device = $request->input('device', 'fan');
      $value  = $request->input('value');

      $result = match ($action) {
         'turn_on'  => $this->turnOn($device),
         'turn_off' => $this->turnOff($device),
         'set_speed' => $this->setSpeed($device, $value),
         'get_temperature' => $this->getTemperature($device),
         'get_humidity' => $this->getHumidity($device),
         'toggle_all' => $this->toggleAll(),
         default => [
            'success' => false,
            'message' => "Unknown action: {$action}",
         ],
      };

      Log::info('Sinric Pro command executed', [
         'action' => $action,
         'device' => $device,
         'value'  => $value,
         'result' => $result,
      ]);

      return response()->json($result);
   }

   /**
    * Sinric Pro webhook callback.
    * This endpoint receives real callbacks from the Sinric Pro cloud
    * when Google Assistant triggers a voice command.
    *
    * POST /api/sinric/callback
    */
   public function callback(Request $request)
   {
      $payload = $request->all();

      Log::info('Sinric Pro callback received', $payload);

      // Extract action from Sinric Pro payload format
      $action = $payload['action'] ?? $payload['value']['action'] ?? null;

      if (!$action) {
         return response()->json(['success' => false, 'message' => 'No action in payload'], 400);
      }

      // Map Sinric Pro action names to our internal actions
      $actionMap = [
         'setPowerState'  => $payload['value']['state'] ?? 'Off' === 'On' ? 'turn_on' : 'turn_off',
         'setRangeValue'  => 'set_speed',
         'setPercentage'  => 'set_speed',
      ];

      $internalAction = $actionMap[$action] ?? $action;
      $value = $payload['value']['rangeValue']
         ?? $payload['value']['percentage']
         ?? $payload['value']['state']
         ?? null;

      // Convert percentage to 0-255 range for fan speed
      if (is_numeric($value) && $value <= 100) {
         $value = (int) round($value * 255 / 100);
      }

      $result = match ($internalAction) {
         'turn_on'   => $this->turnOn('fan'),
         'turn_off'  => $this->turnOff('fan'),
         'set_speed' => $this->setSpeed('fan', $value),
         default     => ['success' => false, 'message' => "Unmapped action: {$action}"],
      };

      return response()->json([
         'success' => $result['success'],
         'message' => $result['message'],
      ]);
   }

   // ─── Private helpers ──────────────────────────

   private function turnOn(string $device): array
   {
      Cache::forever('fan_state', 'on');

      return [
         'success' => true,
         'message' => "OK, turning the {$device} on.",
         'state'   => 'on',
      ];
   }

   private function turnOff(string $device): array
   {
      Cache::forever('fan_state', 'off');

      return [
         'success' => true,
         'message' => "OK, turning the {$device} off.",
         'state'   => 'off',
      ];
   }

   private function setSpeed(string $device, mixed $value): array
   {
      $speed = max(0, min(255, (int) round($value * 255 / 100)));
      Cache::forever('fan_speed', $speed);

      return [
         'success' => true,
         'message' => "OK, setting the {$device} speed to {$value}%.",
         'speed'   => $speed,
         'percent' => (int) $value,
      ];
   }

   private function getTemperature(string $device): array
   {
      // Get latest sensor reading from database
      $latest = \App\Models\SensorData::latest('recorded_at')->first();

      $temp = $latest?->temperature ?? Cache::get('last_temperature', 25.0);

      return [
         'success'     => true,
         'message'     => "The current temperature is {$temp}°C.",
         'temperature' => $temp,
      ];
   }

   private function getHumidity(string $device): array
   {
      $latest = \App\Models\SensorData::latest('recorded_at')->first();

      $humidity = $latest?->humidity ?? Cache::get('last_humidity', 60.0);

      return [
         'success'  => true,
         'message'  => "The current humidity is {$humidity}%.",
         'humidity' => $humidity,
      ];
   }

   private function toggleAll(): array
   {
      $currentState = Cache::get('fan_state', 'off');
      $newState = $currentState === 'on' ? 'off' : 'on';
      Cache::forever('fan_state', $newState);

      return [
         'success' => true,
         'message' => "OK, all devices toggled {$newState}.",
         'state'   => $newState,
      ];
   }
}
