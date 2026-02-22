@extends('layouts.app')
@section('title', 'Alerts — IoT Monitor')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
   <h1 class="text-xl font-semibold text-black">Alerts</h1>
   <div class="flex items-center gap-3">
      @if($unreadCount > 0)
      <form method="POST" action="{{ route('alerts.mark-all-read') }}">
         @csrf
         <button type="submit"
            class="text-xs text-neutral-400 hover:text-black underline underline-offset-2 transition-colors">
            Mark all as read
         </button>
      </form>
      @endif
      <a href="{{ route('alerts.create') }}"
         class="px-3 py-1.5 bg-black text-white text-xs rounded hover:bg-neutral-800 transition-colors whitespace-nowrap">
         + New Alert Rule
      </a>
   </div>
</div>

{{-- Summary --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-6">
   <div class="border border-neutral-200 rounded-lg p-3 sm:p-4">
      <p class="text-[10px] text-neutral-400 uppercase tracking-wider">Total Rules</p>
      <p class="text-xl sm:text-2xl font-bold mt-1">{{ $totalAlerts }}</p>
   </div>
   <div class="border border-neutral-200 rounded-lg p-3 sm:p-4">
      <p class="text-[10px] text-neutral-400 uppercase tracking-wider">Active</p>
      <p class="text-xl sm:text-2xl font-bold mt-1">{{ $activeAlerts }}</p>
   </div>
   <div class="border border-neutral-200 rounded-lg p-3 sm:p-4">
      <p class="text-[10px] text-neutral-400 uppercase tracking-wider">Triggered</p>
      <p class="text-xl sm:text-2xl font-bold mt-1">{{ $triggeredAlerts }}</p>
   </div>
   <div class="border border-neutral-200 rounded-lg p-3 sm:p-4">
      <p class="text-[10px] text-neutral-400 uppercase tracking-wider">Unread</p>
      <p class="text-xl sm:text-2xl font-bold mt-1">{{ $unreadCount }}</p>
   </div>
</div>

{{-- Unread Alerts --}}
@if($unreadAlerts->count())
<div class="border border-neutral-200 rounded-lg p-4 sm:p-5 mb-6">
   <h3 class="text-sm font-semibold mb-3">Unread Alerts</h3>
   <div class="divide-y divide-neutral-100">
      @foreach($unreadAlerts as $alert)
      <div class="py-3">
         <div class="flex items-start gap-3">
            <span
               class="flex shrink-0 h-2 w-2 rounded-full mt-1.5 {{ $alert->severity === 'critical' ? 'bg-black' : 'bg-neutral-400' }}"></span>
            <div class="flex-1 min-w-0">
               <p class="text-sm text-black break-words">{{ $alert->message }}</p>
               <div class="flex flex-wrap items-center gap-x-2 gap-y-1 mt-1">
                  <p class="text-[10px] text-neutral-400">
                     {{ $alert->device->name ?? 'N/A' }} · {{ ucfirst($alert->severity) }}
                     ·
                     {{ $alert->triggered_at ? $alert->triggered_at->diffForHumans() : $alert->created_at->diffForHumans() }}
                  </p>
                  <form method="POST" action="{{ route('alerts.mark-read', $alert) }}">
                     @csrf @method('PATCH')
                     <button type="submit"
                        class="text-[10px] text-neutral-400 hover:text-black underline underline-offset-2 whitespace-nowrap">
                        Mark read
                     </button>
                  </form>
               </div>
            </div>
         </div>
      </div>
      @endforeach
   </div>
</div>
@endif

{{-- All Alert Rules — Desktop Table --}}
<div class="border border-neutral-200 rounded-lg overflow-hidden hidden md:block">
   <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-neutral-200">
         <thead>
            <tr class="border-b border-neutral-200">
               <th class="px-6 py-3 text-left text-[10px] font-medium text-neutral-400 uppercase tracking-wider">Device</th>
               <th class="px-6 py-3 text-left text-[10px] font-medium text-neutral-400 uppercase tracking-wider">Condition</th>
               <th class="px-6 py-3 text-left text-[10px] font-medium text-neutral-400 uppercase tracking-wider">Severity</th>
               <th class="px-6 py-3 text-left text-[10px] font-medium text-neutral-400 uppercase tracking-wider">Status</th>
               <th class="px-6 py-3 text-left text-[10px] font-medium text-neutral-400 uppercase tracking-wider">Triggered</th>
               <th class="px-6 py-3 text-right text-[10px] font-medium text-neutral-400 uppercase tracking-wider">Actions</th>
            </tr>
         </thead>
         <tbody class="divide-y divide-neutral-100">
            @forelse($alerts as $alert)
            <tr class="hover:bg-neutral-50 transition-colors">
               <td class="px-6 py-4 whitespace-nowrap">
                  <p class="text-sm font-medium text-black">{{ $alert->device->name ?? 'N/A' }}</p>
                  <p class="text-[10px] text-neutral-400">{{ $alert->sensor_type }}</p>
               </td>
               <td class="px-6 py-4 whitespace-nowrap text-xs text-neutral-500">
                  {{ ucfirst($alert->sensor_type) }} {{ $alert->condition }} {{ $alert->threshold_value }}
               </td>
               <td class="px-6 py-4 whitespace-nowrap">
                  <span class="inline-flex items-center space-x-1.5 text-xs">
                     <span class="w-1.5 h-1.5 rounded-full {{ $alert->severity === 'critical' ? 'bg-black' : 'bg-neutral-400' }}"></span>
                     <span class="text-neutral-500">{{ ucfirst($alert->severity) }}</span>
                  </span>
               </td>
               <td class="px-6 py-4 whitespace-nowrap">
                  <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium border {{ $alert->is_active ? 'border-black text-black' : 'border-neutral-300 text-neutral-400' }}">
                     {{ $alert->is_active ? 'Active' : 'Inactive' }}
                  </span>
               </td>
               <td class="px-6 py-4 whitespace-nowrap text-xs text-neutral-400">
                  {{ $alert->triggered_at ? $alert->triggered_at->diffForHumans() : '—' }}
               </td>
               <td class="px-6 py-4 whitespace-nowrap text-right space-x-3">
                  <form method="POST" action="{{ route('alerts.destroy', $alert) }}" class="inline"
                     onsubmit="return confirm('Delete this alert rule?')">
                     @csrf @method('DELETE')
                     <button type="submit"
                        class="text-[10px] text-neutral-400 hover:text-black underline underline-offset-2">
                        Delete
                     </button>
                  </form>
               </td>
            </tr>
            @empty
            <tr>
               <td colspan="6" class="px-6 py-8 text-center">
                  <p class="text-sm text-neutral-400">No alert rules created yet</p>
                  <a href="{{ route('alerts.create') }}"
                     class="mt-2 inline-block text-xs underline text-neutral-500 hover:text-black">
                     Create your first alert rule
                  </a>
               </td>
            </tr>
            @endforelse
         </tbody>
      </table>
   </div>
</div>

{{-- All Alert Rules — Mobile Cards --}}
<div class="md:hidden space-y-3">
   @forelse($alerts as $alert)
   <div class="border border-neutral-200 rounded-lg p-4">
      <div class="flex items-start justify-between gap-3">
         <div class="min-w-0">
            <p class="text-sm font-medium text-black truncate">{{ $alert->device->name ?? 'N/A' }}</p>
            <p class="text-[10px] text-neutral-400 mt-0.5">{{ $alert->sensor_type }}</p>
         </div>
         <span class="shrink-0 inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium border {{ $alert->is_active ? 'border-black text-black' : 'border-neutral-300 text-neutral-400' }}">
            {{ $alert->is_active ? 'Active' : 'Inactive' }}
         </span>
      </div>

      <div class="mt-3 space-y-2">
         <div class="flex items-center justify-between">
            <span class="text-[10px] text-neutral-400 uppercase tracking-wider">Condition</span>
            <span class="text-xs text-neutral-500">
               {{ ucfirst($alert->sensor_type) }} {{ $alert->condition }} {{ $alert->threshold_value }}
            </span>
         </div>
         <div class="flex items-center justify-between">
            <span class="text-[10px] text-neutral-400 uppercase tracking-wider">Severity</span>
            <span class="inline-flex items-center space-x-1.5 text-xs">
               <span class="w-1.5 h-1.5 rounded-full {{ $alert->severity === 'critical' ? 'bg-black' : 'bg-neutral-400' }}"></span>
               <span class="text-neutral-500">{{ ucfirst($alert->severity) }}</span>
            </span>
         </div>
         <div class="flex items-center justify-between">
            <span class="text-[10px] text-neutral-400 uppercase tracking-wider">Triggered</span>
            <span class="text-xs text-neutral-400">
               {{ $alert->triggered_at ? $alert->triggered_at->diffForHumans() : '—' }}
            </span>
         </div>
      </div>

      <div class="mt-3 pt-3 border-t border-neutral-100 flex justify-end">
         <form method="POST" action="{{ route('alerts.destroy', $alert) }}" class="inline"
            onsubmit="return confirm('Delete this alert rule?')">
            @csrf @method('DELETE')
            <button type="submit"
               class="text-[10px] text-neutral-400 hover:text-black underline underline-offset-2">
               Delete
            </button>
         </form>
      </div>
   </div>
   @empty
   <div class="border border-neutral-200 rounded-lg p-6 text-center">
      <p class="text-sm text-neutral-400">No alert rules created yet</p>
      <a href="{{ route('alerts.create') }}"
         class="mt-2 inline-block text-xs underline text-neutral-500 hover:text-black">
         Create your first alert rule
      </a>
   </div>
   @endforelse
</div>

@if($alerts->hasPages())
<div class="mt-4">
   {{ $alerts->links() }}
</div>
@endif
@endsection
