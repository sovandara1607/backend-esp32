<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Alert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AlertController extends Controller
{
   /**
    * Get all alerts for the authenticated user.
    */
   public function index(Request $request)
   {
      $query = Auth::user()->alerts()->with('device')->latest();

      if ($request->has('unread')) {
         $query->where('is_read', false);
      }

      if ($request->has('severity')) {
         $query->where('severity', $request->severity);
      }

      $alerts = $query->paginate($request->input('per_page', 20));

      return response()->json($alerts);
   }

   /**
    * Create an alert rule.
    */
   public function store(Request $request)
   {
      $validated = $request->validate([
         'device_id' => 'required|exists:devices,id',
         'alert_type' => 'required|string|in:threshold,offline,custom',
         'sensor_type' => 'required_if:alert_type,threshold|string|max:50',
         'condition' => 'required_if:alert_type,threshold|string|in:above,below,equals',
         'threshold_value' => 'required_if:alert_type,threshold|numeric',
         'message' => 'required|string|max:500',
         'severity' => 'required|in:info,warning,critical',
      ]);

      $validated['user_id'] = Auth::id();
      $validated['is_active'] = true;

      $alert = Alert::create($validated);

      return response()->json(['alert' => $alert], 201);
   }

   /**
    * Mark alert as read.
    */
   public function markRead(Alert $alert)
   {
      if ($alert->user_id !== Auth::id()) {
         abort(403);
      }

      $alert->update(['is_read' => true]);
      return response()->json(['alert' => $alert]);
   }

   /**
    * Mark all alerts as read.
    */
   public function markAllRead()
   {
      Auth::user()->alerts()->where('is_read', false)->update(['is_read' => true]);
      return response()->json(['message' => 'All alerts marked as read.']);
   }

   /**
    * Delete an alert rule.
    */
   public function destroy(Alert $alert)
   {
      if ($alert->user_id !== Auth::id()) {
         abort(403);
      }

      $alert->delete();
      return response()->json(['message' => 'Alert deleted.']);
   }
}
