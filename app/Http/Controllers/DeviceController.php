<?php

namespace App\Http\Controllers;

use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class DeviceController extends Controller
{
    /**
     * Display a listing of the user's devices.
     */
    public function index()
    {
        $devices = Auth::user()->devices()->latest()->get();
        return view('devices.index', compact('devices'));
    }

    /**
     * Show the form for creating a new device.
     */
    public function create()
    {
        return view('devices.create');
    }

    /**
     * Store a newly created device.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'device_type' => 'required|string|max:100',
            'device_identifier' => 'required|string|max:255|unique:devices',
            'description' => 'nullable|string|max:1000',
            'location' => 'nullable|string|max:255',
        ]);

        $validated['user_id'] = Auth::id();

        $device = Device::create($validated);

        return redirect()->route('devices.show', $device)
            ->with('success', 'Device registered successfully.');
    }

    /**
     * Display the specified device.
     */
    public function show(Device $device)
    {
        $this->authorizeDevice($device);

        $device->load(['sensorData' => function ($q) {
            $q->latest('recorded_at')->limit(50);
        }]);

        $latestData = $device->sensorData()
            ->selectRaw('sensor_type, value, unit, recorded_at')
            ->whereIn('id', function ($query) use ($device) {
                $query->selectRaw('MAX(id)')
                    ->from('sensor_data')
                    ->where('device_id', $device->id)
                    ->groupBy('sensor_type');
            })
            ->get()
            ->keyBy('sensor_type');

        return view('devices.show', compact('device', 'latestData'));
    }

    /**
     * Show the form for editing a device.
     */
    public function edit(Device $device)
    {
        $this->authorizeDevice($device);
        return view('devices.edit', compact('device'));
    }

    /**
     * Update the specified device.
     */
    public function update(Request $request, Device $device)
    {
        $this->authorizeDevice($device);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'device_type' => 'required|string|max:100',
            'description' => 'nullable|string|max:1000',
            'location' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $device->update($validated);

        return redirect()->route('devices.show', $device)
            ->with('success', 'Device updated successfully.');
    }

    /**
     * Remove the specified device.
     */
    public function destroy(Device $device)
    {
        $this->authorizeDevice($device);
        $device->delete();
        return redirect()->route('devices.index')
            ->with('success', 'Device deleted successfully.');
    }

    /**
     * Send a command to a device.
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

        // Sync with fan cache so ESP32 picks it up via /api/fan/status
        match ($validated['command']) {
            'on' => Cache::forever('fan_state', 'on'),
            'off' => Cache::forever('fan_state', 'off'),
            'set_speed' => Cache::forever('fan_speed', $validated['payload']['speed'] ?? 255),
            default => null,
        };

        return back()->with('success', "Command '{$validated['command']}' sent to device.");
    }

    /**
     * Ensure the authenticated user owns this device.
     */
    private function authorizeDevice(Device $device): void
    {
        if ($device->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this device.');
        }
    }
}
