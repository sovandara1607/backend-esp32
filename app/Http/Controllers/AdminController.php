<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Device;
use App\Models\SensorData;
use App\Models\Alert;
use Illuminate\Http\Request;

class AdminController extends Controller
{
   /**
    * Admin dashboard overview.
    */
   public function index()
   {
      $stats = [
         'total_users' => User::count(),
         'total_devices' => Device::count(),
         'online_devices' => Device::where('status', 'online')->count(),
         'total_alerts' => Alert::whereNull('triggered_at')->where('is_active', true)->count(),
         'triggered_alerts' => Alert::whereNotNull('triggered_at')->where('is_read', false)->count(),
         'sensor_readings_today' => SensorData::whereDate('recorded_at', today())->count(),
      ];

      $recentUsers = User::latest()->limit(10)->get();
      $recentDevices = Device::with('user')->latest()->limit(10)->get();
      $recentAlerts = Alert::with(['device', 'user'])
         ->whereNotNull('triggered_at')
         ->latest('triggered_at')
         ->limit(10)
         ->get();

      return view('admin.dashboard', compact('stats', 'recentUsers', 'recentDevices', 'recentAlerts'));
   }

   /**
    * List all users.
    */
   public function users()
   {
      $users = User::withCount('devices')->latest()->paginate(20);
      return view('admin.users', compact('users'));
   }

   /**
    * List all devices.
    */
   public function devices()
   {
      $devices = Device::with('user')->latest()->paginate(20);
      return view('admin.devices', compact('devices'));
   }

   /**
    * Toggle admin status of a user.
    */
   public function toggleAdmin(User $user)
   {
      $user->update(['is_admin' => !$user->is_admin]);
      return back()->with('success', "User {$user->name} admin status updated.");
   }

   /**
    * Delete a user and their devices.
    */
   public function deleteUser(User $user)
   {
      $user->delete();
      return back()->with('success', 'User deleted.');
   }
}
