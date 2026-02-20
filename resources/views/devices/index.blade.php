@extends('layouts.app')
@section('title', 'Devices')

@section('content')
<div class="flex items-center justify-between mb-5">
   <h2 class="text-lg font-semibold">My Devices</h2>
   <a href="{{ route('devices.create') }}"
      class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded bg-black text-white hover:bg-neutral-800 transition">
      <svg class="w-3.5 h-3.5 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
         <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
      </svg>
      Add Device
   </a>
</div>

@if($devices->count())
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
   @foreach($devices as $device)
   <div class="border border-neutral-200 rounded-lg hover:border-neutral-400 transition-colors">
      <div class="p-4">
         <div class="flex items-start justify-between">
            <div class="flex items-center space-x-2.5">
               <div class="w-1.5 h-1.5 rounded-full {{ $device->status === 'online' ? 'bg-black' : 'bg-neutral-300' }}">
               </div>
               <h3 class="text-sm font-semibold">{{ $device->name }}</h3>
            </div>
            <span
               class="text-[10px] font-medium px-2 py-0.5 rounded border {{ $device->status === 'online' ? 'border-black text-black' : 'border-neutral-300 text-neutral-400' }}">
               {{ ucfirst($device->status) }}
            </span>
         </div>

         <div class="mt-3 space-y-1.5">
            <div class="flex items-center text-[11px] text-neutral-500">
               <svg class="w-3.5 h-3.5 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round"
                     d="M12 12c-2.5-2.5-5-3-7-1s-1.5 4.5 1 7m6-6c2.5-2.5 3-5 1-7s-4.5-1.5-7 1m6 6c2.5 2.5 5 3 7 1s1.5-4.5-1-7m-6 6c-2.5 2.5-3 5-1 7s4.5 1.5 7-1" />
                  <circle cx="12" cy="12" r="1" fill="currentColor" />
               </svg>
               {{ $device->device_type }} &middot; {{ $device->device_identifier }}
            </div>
            @if($device->location)
            <div class="flex items-center text-[11px] text-neutral-500">
               <svg class="w-3.5 h-3.5 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round"
                     d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
               </svg>
               {{ $device->location }}
            </div>
            @endif
            @if($device->last_seen_at)
            <p class="text-[10px] text-neutral-400">Last seen: {{ $device->last_seen_at->diffForHumans() }}</p>
            @endif
         </div>
      </div>
      <div class="px-4 py-2.5 border-t border-neutral-100 flex justify-between">
         <a href="{{ route('devices.show', $device) }}" class="text-xs font-medium hover:underline">View</a>
         <div class="flex space-x-3">
            <a href="{{ route('devices.edit', $device) }}" class="text-xs text-neutral-500 hover:text-black">Edit</a>
            <form method="POST" action="{{ route('devices.destroy', $device) }}"
               onsubmit="return confirm('Delete this device?')">
               @csrf @method('DELETE')
               <button type="submit" class="text-xs text-neutral-500 hover:text-black">Delete</button>
            </form>
         </div>
      </div>
   </div>
   @endforeach
</div>
@else
<div class="border border-neutral-200 rounded-lg p-10 text-center">
   <p class="text-sm text-neutral-500">No devices registered</p>
   <p class="text-xs text-neutral-400 mt-1">Get started by registering your first IoT device.</p>
   <a href="{{ route('devices.create') }}"
      class="mt-3 inline-flex items-center px-3 py-1.5 text-xs font-medium rounded bg-black text-white hover:bg-neutral-800">
      Register Device
   </a>
</div>
@endif
@endsection