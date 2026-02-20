@extends('layouts.app')
@section('title', 'Admin Dashboard — IoT Monitor')

@section('content')
<h1 class="text-xl font-semibold text-black mb-6">Admin Dashboard</h1>

{{-- Stats Grid --}}
<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-8">
   @foreach([
   ['label' => 'Users', 'value' => $stats['total_users']],
   ['label' => 'Devices', 'value' => $stats['total_devices']],
   ['label' => 'Online', 'value' => $stats['online_devices']],
   ['label' => 'Alert Rules', 'value' => $stats['total_alerts']],
   ['label' => 'Triggered', 'value' => $stats['triggered_alerts']],
   ['label' => 'Readings Today', 'value' => $stats['sensor_readings_today']],
   ] as $stat)
   <div class="border border-neutral-200 rounded-lg p-4 text-center">
      <p class="text-2xl font-bold text-black">{{ $stat['value'] }}</p>
      <p class="text-[10px] font-medium text-neutral-400 mt-1 uppercase tracking-wider">{{ $stat['label'] }}</p>
   </div>
   @endforeach
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
   {{-- Recent Users --}}
   <div class="border border-neutral-200 rounded-lg p-5">
      <div class="flex items-center justify-between mb-4">
         <h3 class="text-sm font-semibold text-black">Recent Users</h3>
         <a href="{{ route('admin.users') }}" class="text-xs text-neutral-400 hover:text-black transition-colors">View All →</a>
      </div>
      <div class="divide-y divide-neutral-100">
         @foreach($recentUsers as $user)
         <div class="flex items-center justify-between py-3">
            <div>
               <p class="text-sm font-medium text-black">{{ $user->name }}</p>
               <p class="text-xs text-neutral-400">{{ $user->email }}</p>
            </div>
            <div class="flex items-center space-x-2">
               @if($user->isAdmin())
               <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium border border-black text-black">Admin</span>
               @endif
               <span class="text-[10px] text-neutral-400">{{ $user->created_at->diffForHumans() }}</span>
            </div>
         </div>
         @endforeach
      </div>
   </div>

   {{-- Recent Devices --}}
   <div class="border border-neutral-200 rounded-lg p-5">
      <div class="flex items-center justify-between mb-4">
         <h3 class="text-sm font-semibold text-black">Recent Devices</h3>
         <a href="{{ route('admin.devices') }}" class="text-xs text-neutral-400 hover:text-black transition-colors">View All →</a>
      </div>
      <div class="divide-y divide-neutral-100">
         @foreach($recentDevices as $device)
         <div class="flex items-center justify-between py-3">
            <div>
               <p class="text-sm font-medium text-black">{{ $device->name }}</p>
               <p class="text-xs text-neutral-400">{{ $device->user->name ?? 'N/A' }} · {{ $device->device_type }}</p>
            </div>
            <span class="inline-flex items-center space-x-1.5 text-xs">
               <span class="w-1.5 h-1.5 rounded-full {{ $device->status === 'online' ? 'bg-black' : 'bg-neutral-300' }}"></span>
               <span class="text-neutral-500">{{ ucfirst($device->status) }}</span>
            </span>
         </div>
         @endforeach
      </div>
   </div>
</div>

{{-- Triggered Alerts --}}
<div class="mt-6 border border-neutral-200 rounded-lg p-5">
   <h3 class="text-sm font-semibold text-black mb-4">Recent Triggered Alerts</h3>
   @forelse($recentAlerts as $alert)
   <div class="flex items-start justify-between py-3 {{ !$loop->last ? 'border-b border-neutral-100' : '' }}">
      <div class="flex items-start space-x-3">
         <span class="flex h-2 w-2 rounded-full mt-1.5 {{ $alert->severity === 'critical' ? 'bg-black' : 'bg-neutral-400' }}"></span>
         <div>
            <p class="text-sm text-black">{{ $alert->message }}</p>
            <p class="text-xs text-neutral-400">{{ $alert->device->name ?? 'N/A' }} · {{ $alert->user->name ?? 'N/A' }}</p>
         </div>
      </div>
      <span class="text-[10px] text-neutral-400 whitespace-nowrap ml-4">{{ $alert->triggered_at?->diffForHumans() }}</span>
   </div>
   @empty
   <p class="text-center text-neutral-400 text-sm py-4">No triggered alerts</p>
   @endforelse
</div>
@endsection