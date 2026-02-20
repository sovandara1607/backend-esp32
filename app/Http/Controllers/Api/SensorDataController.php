<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\SensorData;
use App\Models\Alert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SensorDataController extends Controller
{
   /**
    * Store sensor data (ESP32 pushes data here).
    * Supports both authenticated and device-key-based access.
    */
   public function store(Request $request)
   {
      $validated = $request->validate([
         'device_identifier' => 'required|string|exists:devices,device_identifier',
         'readings' => 'required|array|min:1',
         'readings.*.sensor_type' => 'required|string|max:50',
         'readings.*.value' => 'required|numeric',
         'readings.*.unit' => 'nullable|string|max:20',
      ]);

      $device = Device::where('device_identifier', $validated['device_identifier'])->firstOrFail();

      // Update device last seen
      $device->update([
         'status' => 'online',
         'last_seen_at' => now(),
      ]);

      $records = [];
      foreach ($validated['readings'] as $reading) {
         $records[] = SensorData::create([
            'device_id' => $device->id,
            'sensor_type' => $reading['sensor_type'],
            'value' => $reading['value'],
            'unit' => $reading['unit'] ?? null,
            'recorded_at' => now(),
         ]);

         // Check alert thresholds
         $this->checkAlerts($device, $reading['sensor_type'], $reading['value']);
      }

      return response()->json([
         'message' => 'Data recorded.',
         'count' => count($records),
      ], 201);
   }

   /**
    * Get sensor data for a device with optional filters.
    */
   public function index(Request $request, Device $device)
   {
      $query = $device->sensorData();

      if ($request->has('sensor_type')) {
         $query->where('sensor_type', $request->sensor_type);
      }

      if ($request->has('from')) {
         $query->where('recorded_at', '>=', $request->from);
      }

      if ($request->has('to')) {
         $query->where('recorded_at', '<=', $request->to);
      }

      $limit = min($request->input('limit', 100), 1000);
      $data = $query->latest('recorded_at')->limit($limit)->get();

      return response()->json(['data' => $data]);
   }

   /**
    * Get chart-ready aggregated data.
    */
   public function chartData(Request $request, Device $device)
   {
      $sensorType = $request->input('sensor_type', 'temperature');
      $hours = $request->input('hours', 24);

      $data = $device->sensorData()
         ->where('sensor_type', $sensorType)
         ->where('recorded_at', '>=', now()->subHours($hours))
         ->orderBy('recorded_at')
         ->get(['value', 'recorded_at']);

      return response()->json([
         'labels' => $data->pluck('recorded_at')->map->format('H:i'),
         'values' => $data->pluck('value'),
         'sensor_type' => $sensorType,
      ]);
   }

   /**
    * Get latest reading for each sensor type on a device.
    */
   public function latest(Device $device)
   {
      $latest = $device->sensorData()
         ->selectRaw('sensor_type, value, unit, recorded_at')
         ->whereIn('id', function ($query) use ($device) {
            $query->selectRaw('MAX(id)')
               ->from('sensor_data')
               ->where('device_id', $device->id)
               ->groupBy('sensor_type');
         })
         ->get();

      return response()->json(['data' => $latest]);
   }

   /**
    * Check if any alert thresholds are exceeded.
    */
   private function checkAlerts(Device $device, string $sensorType, float $value): void
   {
      $alertRules = Alert::where('device_id', $device->id)
         ->where('sensor_type', $sensorType)
         ->where('is_active', true)
         ->whereNull('triggered_at')
         ->get();

      foreach ($alertRules as $rule) {
         $triggered = false;

         if ($rule->condition === 'above' && $value > $rule->threshold_value) {
            $triggered = true;
         } elseif ($rule->condition === 'below' && $value < $rule->threshold_value) {
            $triggered = true;
         } elseif ($rule->condition === 'equals' && $value == $rule->threshold_value) {
            $triggered = true;
         }

         if ($triggered) {
            // Create a notification alert
            Alert::create([
               'device_id' => $device->id,
               'user_id' => $device->user_id,
               'alert_type' => 'threshold',
               'sensor_type' => $sensorType,
               'condition' => $rule->condition,
               'threshold_value' => $rule->threshold_value,
               'message' => "{$device->name}: {$sensorType} is {$value} ({$rule->condition} {$rule->threshold_value})",
               'severity' => $rule->severity,
               'triggered_at' => now(),
            ]);
         }
      }
   }
}
