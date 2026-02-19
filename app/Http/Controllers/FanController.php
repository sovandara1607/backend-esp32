<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Cache;

class FanController extends \App\Http\Controllers\Controller
{
    // The ESP32 calls this
    public function getStatus() {
        return response()->json([
            'status' => Cache::get('fan_state', 'off'),
            'speed' => 255
        ]);
    }

    public function toggle() {
        $currentState = Cache::get('fan_state', 'off');
        $newState = ($currentState == 'on') ? 'off' : 'on';
        Cache::put('fan_state', $newState);

        return "Fan is now: " . $newState;
    }
}
