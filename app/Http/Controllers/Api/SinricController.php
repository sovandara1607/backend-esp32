<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\SensorData;
use App\Models\TemperatureProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SinricController extends Controller
{
   /**
    * Check Sinric Pro connection status.
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
      $deviceName = $request->input('device', 'fan');
      $value  = $request->input('value');

      // Resolve to actual Device model by identifier, or fall back to first device
      $device = Device::where('device_identifier', $deviceName)->first()
                ?? Device::first();

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
         'device' => $deviceName,
         'value'  => $value,
         'result' => $result,
      ]);

      return response()->json($result);
   }

   /**
    * Sinric Pro webhook callback.
    * Receives real callbacks from the Sinric Pro cloud
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
         'setPowerState'  => ($payload['value']['state'] ?? 'Off') === 'On' ? 'turn_on' : 'turn_off',
         'setRangeValue'  => 'set_speed',
         'setPercentage'  => 'set_speed',
      ];

      $internalAction = $actionMap[$action] ?? $action;
      $value = $payload['value']['rangeValue']
         ?? $payload['value']['percentage']
         ?? $payload['value']['state']
         ?? null;

      // Resolve device
      $device = Device::first();

      $result = match ($internalAction) {
         'turn_on'   => $this->turnOn($device),
         'turn_off'  => $this->turnOff($device),
         'set_speed' => $this->setSpeed($device, $value),
         default     => ['success' => false, 'message' => "Unmapped action: {$action}"],
      };

      return response()->json([
         'success' => $result['success'],
         'message' => $result['message'],
      ]);
   }

   // ─── Private helpers ──────────────────────────

   private function deactivateTempControl(?Device $device): void
   {
      if (!$device) return;

      if (Cache::get("temp_control_active_{$device->id}")) {
         Cache::forget("temp_control_active_{$device->id}");
         Cache::forget("temp_control_profile_id_{$device->id}");
         TemperatureProfile::where('is_active', true)
            ->where('user_id', $device->user_id)
            ->update(['is_active' => false]);
      }
   }

   private function turnOn(?Device $device): array
   {
      if (!$device) {
         return ['success' => false, 'message' => 'No device found.'];
      }

      Cache::forever("fan_state_{$device->id}", 'on');
      $this->deactivateTempControl($device);

      return [
         'success' => true,
         'message' => "OK, turning the {$device->name} on.",
         'state'   => 'on',
      ];
   }

   private function turnOff(?Device $device): array
   {
      if (!$device) {
         return ['success' => false, 'message' => 'No device found.'];
      }

      Cache::forever("fan_state_{$device->id}", 'off');
      $this->deactivateTempControl($device);

      return [
         'success' => true,
         'message' => "OK, turning the {$device->name} off.",
         'state'   => 'off',
      ];
   }

   private function setSpeed(?Device $device, mixed $value): array
   {
      if (!$device) {
         return ['success' => false, 'message' => 'No device found.'];
      }

      $percent = max(0, min(100, (int) $value));
      $speed = (int) round($percent * 255 / 100);
      Cache::forever("fan_speed_{$device->id}", $speed);
      $this->deactivateTempControl($device);

      return [
         'success' => true,
         'message' => "OK, setting the {$device->name} speed to {$percent}%.",
         'speed'   => $speed,
         'percent' => $percent,
      ];
   }

   private function getTemperature(?Device $device): array
   {
      $latest = SensorData::where('sensor_type', 'temperature')
         ->latest('recorded_at')
         ->first();

      $temp = $latest?->value ?? Cache::get('last_temperature', 25.0);

      return [
         'success'     => true,
         'message'     => "The current temperature is {$temp} degrees Celsius.",
         'temperature' => (float) $temp,
      ];
   }

   private function getHumidity(?Device $device): array
   {
      $latest = SensorData::where('sensor_type', 'humidity')
         ->latest('recorded_at')
         ->first();

      $humidity = $latest?->value ?? Cache::get('last_humidity', 60.0);

      return [
         'success'  => true,
         'message'  => "The current humidity is {$humidity} percent.",
         'humidity' => (float) $humidity,
      ];
   }

   private function toggleAll(): array
   {
      $devices = Device::all();
      foreach ($devices as $device) {
         $currentState = Cache::get("fan_state_{$device->id}", 'off');
         $newState = $currentState === 'on' ? 'off' : 'on';
         Cache::forever("fan_state_{$device->id}", $newState);
      }

      return [
         'success' => true,
         'message' => 'OK, all devices toggled.',
         'state'   => 'toggled',
      ];
   }
}
