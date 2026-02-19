<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class FanController extends Controller
{
    /**
     * ESP32 polls this endpoint to get current commands.
     */
    public function getStatus()
    {
        $speed = (int) Cache::get('fan_speed', 0);

        return response()->json([
            'mode'   => Cache::get('fan_mode', 'manual'),
            'speed'  => $speed,
            'status' => $speed > 0 ? 'on' : 'off',
        ]);
    }

    /**
     * Web UI polls this for complete dashboard state.
     */
    public function getFullState()
    {
        $speed = (int) Cache::get('fan_speed', 0);

        return response()->json([
            'mode'                   => Cache::get('fan_mode', 'manual'),
            'speed'                  => $speed,
            'status'                 => $speed > 0 ? 'on' : 'off',
            'temperature'            => Cache::get('fan_temperature'),
            'temperature_updated_at' => Cache::get('fan_temperature_updated_at'),
        ]);
    }

    /**
     * Set fan speed (0-100). Used by manual slider and voice commands.
     */
    public function setSpeed(Request $request)
    {
        $request->validate([
            'speed' => 'required|integer|min:0|max:100',
        ]);

        $speed = (int) $request->input('speed');
        Cache::forever('fan_speed', $speed);

        return response()->json([
            'success' => true,
            'speed'   => $speed,
            'status'  => $speed > 0 ? 'on' : 'off',
        ]);
    }

    /**
     * Switch control mode: manual, voice, temperature.
     */
    public function setMode(Request $request)
    {
        $request->validate([
            'mode' => 'required|in:manual,voice,temperature',
        ]);

        $mode = $request->input('mode');
        Cache::forever('fan_mode', $mode);

        return response()->json([
            'success' => true,
            'mode'    => $mode,
        ]);
    }

    /**
     * ESP32 reports current temperature reading from LM35 sensor.
     */
    public function reportTemperature(Request $request)
    {
        $request->validate([
            'temperature' => 'required|numeric|min:-10|max:100',
        ]);

        $temp = (float) $request->input('temperature');
        Cache::forever('fan_temperature', $temp);
        Cache::forever('fan_temperature_updated_at', now()->toIso8601String());

        return response()->json([
            'success'     => true,
            'temperature' => $temp,
        ]);
    }
}
