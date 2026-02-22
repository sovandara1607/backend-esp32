<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\SensorData;
use App\Models\Alert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    /**
     * Display the unified dashboard.
     */
    public function index()
    {
        $user = Auth::user();
        $devices = $user->devices()->get();

        $totalDevices = $devices->count();
        $onlineDevices = $devices->where('status', 'online')->count();
        $offlineDevices = $devices->where('status', 'offline')->count();

        $unreadAlerts = $user->alerts()->where('is_read', false)->latest()->limit(10)->get();
        $unreadAlertCount = $user->alerts()->where('is_read', false)->count();

        // Recent sensor data across all user devices
        $deviceIds = $devices->pluck('id');
        $recentSensorData = SensorData::whereIn('device_id', $deviceIds)
            ->latest('recorded_at')
            ->limit(20)
            ->with('device')
            ->get();

        // Fan status
        $fanStatus = [
            'status' => Cache::get('fan_state', 'off'),
            'speed' => (int) Cache::get('fan_speed', 255),
        ];

        // Temperature control data
        $profiles = $user->temperatureProfiles()->with('rules')->latest()->get();
        $activeProfile = $profiles->firstWhere('is_active', true);
        $tempControlActive = Cache::get('temp_control_active', false);

        return view('dashboard', compact(
            'devices',
            'totalDevices',
            'onlineDevices',
            'offlineDevices',
            'unreadAlerts',
            'unreadAlertCount',
            'recentSensorData',
            'fanStatus',
            'profiles',
            'activeProfile',
            'tempControlActive',
        ));
    }
}
