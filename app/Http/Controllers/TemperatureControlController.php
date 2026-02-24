<?php

namespace App\Http\Controllers;

use App\Models\TemperatureProfile;
use App\Models\TemperatureRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class TemperatureControlController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $profiles = $user->temperatureProfiles()->with('rules')->latest()->get();
        $activeProfile = $profiles->firstWhere('is_active', true);
        $devices = $user->devices;

        $device = $devices->first();
        $fanStatus = [
            'status' => $device ? Cache::get("fan_state_{$device->id}", 'off') : 'off',
            'speed'  => $device ? (int) Cache::get("fan_speed_{$device->id}", 255) : 255,
        ];

        $tempControlActive = $device ? Cache::get("temp_control_active_{$device->id}", false) : false;

        return view('temperature-control', compact(
            'profiles',
            'activeProfile',
            'devices',
            'fanStatus',
            'tempControlActive',
        ));
    }

    public function storeProfile(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
        ]);

        Auth::user()->temperatureProfiles()->create($validated);

        return redirect()->route('temperature-control')
            ->with('success', 'Profile created.');
    }

    public function destroyProfile(TemperatureProfile $profile)
    {
        abort_unless($profile->user_id === Auth::id(), 403);

        if ($profile->is_active) {
            foreach (Auth::user()->devices as $device) {
                Cache::forget("temp_control_active_{$device->id}");
                Cache::forget("temp_control_profile_id_{$device->id}");
            }
        }

        $profile->delete();

        return back()->with('success', 'Profile deleted.');
    }

    public function storeRule(Request $request, TemperatureProfile $profile)
    {
        abort_unless($profile->user_id === Auth::id(), 403);

        $validated = $request->validate([
            'temperature'       => 'required|numeric|min:-10|max:60',
            'fan_speed_percent' => 'required|integer|min:0|max:100',
        ]);

        $profile->rules()->create($validated);

        return back()->with('success', 'Rule added.');
    }

    public function destroyRule(TemperatureProfile $profile, TemperatureRule $rule)
    {
        abort_unless($profile->user_id === Auth::id(), 403);
        abort_unless($rule->temperature_profile_id === $profile->id, 404);

        $rule->delete();

        return back()->with('success', 'Rule removed.');
    }

    public function activate(TemperatureProfile $profile)
    {
        abort_unless($profile->user_id === Auth::id(), 403);

        if ($profile->rules()->count() === 0) {
            return back()->with('error', 'Profile must have at least one rule before activation.');
        }

        // Deactivate all profiles for this user
        Auth::user()->temperatureProfiles()->update(['is_active' => false]);

        // Activate selected
        $profile->update(['is_active' => true]);

        // Set cache flags for all user's devices
        foreach (Auth::user()->devices as $device) {
            Cache::forever("temp_control_active_{$device->id}", true);
            Cache::forever("temp_control_profile_id_{$device->id}", $profile->id);
            Cache::forever("fan_state_{$device->id}", 'on');
        }

        return back()->with('success', 'Temperature control activated with profile: ' . $profile->name);
    }

    public function deactivate()
    {
        Auth::user()->temperatureProfiles()->update(['is_active' => false]);

        foreach (Auth::user()->devices as $device) {
            Cache::forget("temp_control_active_{$device->id}");
            Cache::forget("temp_control_profile_id_{$device->id}");
        }

        return back()->with('success', 'Temperature control deactivated. Fan now in manual mode.');
    }
}
