<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AlertWebController extends Controller
{
   /**
    * Display all alerts for the authenticated user.
    */
   public function index()
   {
      $user = Auth::user();

      $alerts = $user->alerts()->with('device')->latest()->paginate(20);
      $unreadAlerts = $user->alerts()->with('device')->where('is_read', false)->latest()->get();
      $unreadCount = $unreadAlerts->count();
      $totalAlerts = $user->alerts()->count();
      $activeAlerts = $user->alerts()->where('is_active', true)->count();
      $triggeredAlerts = $user->alerts()->whereNotNull('triggered_at')->count();

      return view('alerts.index', compact(
         'alerts',
         'unreadAlerts',
         'unreadCount',
         'totalAlerts',
         'activeAlerts',
         'triggeredAlerts',
      ));
   }

   /**
    * Show the form for creating a new alert rule.
    */
   public function create()
   {
      $devices = Auth::user()->devices;

      return view('alerts.create', compact('devices'));
   }

   /**
    * Store a newly created alert rule.
    */
   public function store(Request $request)
   {
      $validated = $request->validate([
         'device_id' => 'required|exists:devices,id',
         'sensor_type' => 'required|string|in:temperature,humidity,light,pressure,gas',
         'condition' => 'required|string|in:>,<,>=,<=,=',
         'threshold_value' => 'required|numeric',
         'severity' => 'required|string|in:info,warning,critical',
         'message' => 'required|string|max:255',
      ]);

      // Verify device belongs to user
      $device = Device::where('id', $validated['device_id'])
         ->where('user_id', Auth::id())
         ->firstOrFail();

      Auth::user()->alerts()->create(array_merge($validated, [
         'alert_type' => 'threshold',
         'is_active' => true,
      ]));

      return redirect()->route('alerts.index')->with('success', 'Alert rule created.');
   }

   /**
    * Mark a single alert as read.
    */
   public function markRead(Alert $alert)
   {
      abort_unless($alert->user_id === Auth::id(), 403);

      $alert->update(['is_read' => true]);

      return back()->with('success', 'Alert marked as read.');
   }

   /**
    * Mark all alerts as read.
    */
   public function markAllRead()
   {
      Auth::user()->alerts()->where('is_read', false)->update(['is_read' => true]);

      return back()->with('success', 'All alerts marked as read.');
   }

   /**
    * Delete an alert rule.
    */
   public function destroy(Alert $alert)
   {
      abort_unless($alert->user_id === Auth::id(), 403);

      $alert->delete();

      return back()->with('success', 'Alert rule deleted.');
   }
}
