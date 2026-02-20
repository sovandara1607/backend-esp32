@extends('layouts.app')
@section('title', 'Manage Devices — Admin')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
   <h1 class="text-xl font-semibold text-black">All Devices</h1>
   <a href="{{ route('admin.dashboard') }}" class="text-xs text-neutral-400 hover:text-black transition-colors">← Admin
      Dashboard</a>
</div>

<div class="border border-neutral-200 rounded-lg overflow-hidden">
   <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-neutral-200">
         <thead>
            <tr class="border-b border-neutral-200">
               <th class="px-6 py-3 text-left text-[10px] font-medium text-neutral-400 uppercase tracking-wider">Device
               </th>
               <th class="px-6 py-3 text-left text-[10px] font-medium text-neutral-400 uppercase tracking-wider">Owner
               </th>
               <th class="px-6 py-3 text-left text-[10px] font-medium text-neutral-400 uppercase tracking-wider">Type
               </th>
               <th class="px-6 py-3 text-left text-[10px] font-medium text-neutral-400 uppercase tracking-wider">Status
               </th>
               <th class="px-6 py-3 text-left text-[10px] font-medium text-neutral-400 uppercase tracking-wider">Last
                  Seen</th>
               <th class="px-6 py-3 text-left text-[10px] font-medium text-neutral-400 uppercase tracking-wider">Active
               </th>
            </tr>
         </thead>
         <tbody class="divide-y divide-neutral-100">
            @foreach($devices as $device)
            <tr class="hover:bg-neutral-50 transition-colors">
               <td class="px-6 py-4 whitespace-nowrap">
                  <p class="text-sm font-medium text-black">{{ $device->name }}</p>
                  <p class="text-xs text-neutral-400 font-mono">{{ $device->device_identifier }}</p>
               </td>
               <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-500">
                  {{ $device->user->name ?? 'N/A' }}
               </td>
               <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-500">
                  {{ strtoupper($device->device_type) }}
               </td>
               <td class="px-6 py-4 whitespace-nowrap">
                  <span class="inline-flex items-center space-x-1.5 text-xs">
                     <span
                        class="w-1.5 h-1.5 rounded-full {{ $device->status === 'online' ? 'bg-black' : 'bg-neutral-300' }}"></span>
                     <span class="text-neutral-500">{{ ucfirst($device->status) }}</span>
                  </span>
               </td>
               <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-400">
                  {{ $device->last_seen_at ? $device->last_seen_at->diffForHumans() : 'Never' }}
               </td>
               <td class="px-6 py-4 whitespace-nowrap text-xs">
                  @if($device->is_active)
                  <span class="text-black font-medium">Yes</span>
                  @else
                  <span class="text-neutral-400">No</span>
                  @endif
               </td>
            </tr>
            @endforeach
         </tbody>
      </table>
   </div>
</div>

<div class="mt-4">
   {{ $devices->links() }}
</div>
@endsection