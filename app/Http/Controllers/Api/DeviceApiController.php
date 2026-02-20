<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\SensorData;
use App\Models\Alert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DeviceApiController extends Controller
{
   /**
    * List all devices for authenticated user.
    */
   public function index()
   {
      $devices = Auth::user()->devices()->latest()->get();
      return response()->json(['devices' => $devices]);
   }

   /**
    * Store a new device.
    */
   public function store(Request $request)
   {
      $validated = $request->validate([
         'name' => 'required|string|max:255',
         'device_type' => 'required|string|max:100',
         'device_identifier' => 'required|string|max:255|unique:devices',
         'description' => 'nullable|string|max:1000',
         'location' => 'nullable|string|max:255',
         'configuration' => 'nullable|array',
      ]);

      $validated['user_id'] = Auth::id();
      $device = Device::create($validated);

      return response()->json(['device' => $device], 201);
   }

   /**
    * Show a specific device.
    */
   public function show(Device $device)
   {
      $this->authorizeDevice($device);
      $device->load('sensorData', 'alerts', 'commands');
      return response()->json(['device' => $device]);
   }

   /**
    * Update a device.
    */
   public function update(Request $request, Device $device)
   {
      $this->authorizeDevice($device);

      $validated = $request->validate([
         'name' => 'sometimes|string|max:255',
         'device_type' => 'sometimes|string|max:100',
         'description' => 'nullable|string|max:1000',
         'location' => 'nullable|string|max:255',
         'is_active' => 'sometimes|boolean',
         'configuration' => 'nullable|array',
      ]);

      $device->update($validated);
      return response()->json(['device' => $device]);
   }

   /**
    * Delete a device.
    */
   public function destroy(Device $device)
   {
      $this->authorizeDevice($device);
      $device->delete();
      return response()->json(['message' => 'Device deleted.']);
   }

   /**
    * Send command to device.
    */
   public function sendCommand(Request $request, Device $device)
   {
      $this->authorizeDevice($device);

      $validated = $request->validate([
         'command' => 'required|string|max:100',
         'payload' => 'nullable|array',
      ]);

      $command = $device->commands()->create([
         'user_id' => Auth::id(),
         'command' => $validated['command'],
         'payload' => $validated['payload'] ?? null,
         'status' => 'pending',
      ]);

      return response()->json(['command' => $command], 201);
   }

   /**
    * Get pending commands for a device (ESP32 polls this).
    */
   public function pendingCommands(Device $device)
   {
      $commands = $device->commands()
         ->where('status', 'pending')
         ->oldest()
         ->get();

      return response()->json(['commands' => $commands]);
   }

   /**
    * Mark a command as executed (ESP32 calls this).
    */
   public function acknowledgeCommand(Request $request, Device $device, $commandId)
   {
      $command = $device->commands()->findOrFail($commandId);
      $command->update([
         'status' => $request->input('status', 'executed'),
         'response' => $request->input('response'),
         'executed_at' => now(),
      ]);

      return response()->json(['command' => $command]);
   }

   private function authorizeDevice(Device $device): void
   {
      if ($device->user_id !== Auth::id() && !Auth::user()->isAdmin()) {
         abort(403, 'Unauthorized.');
      }
   }
}