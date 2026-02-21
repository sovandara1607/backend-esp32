@extends('layouts.app')
@section('title', 'Add Device')

@section('content')
<div class="max-w-lg mx-auto">
   <div class="mb-5">
      <a href="{{ route('devices.index') }}" class="text-xs text-neutral-400 hover:text-black">&larr; Back to
         Devices</a>
      <h2 class="mt-1.5 text-lg font-semibold">Register New Device</h2>
   </div>

   <div class="border border-neutral-200 rounded-lg p-5">
      <form method="POST" action="{{ route('devices.store') }}" class="space-y-4">
         @csrf

         <div>
            <label for="name" class="block text-xs font-medium text-neutral-600 mb-1">Device Name</label>
            <input type="text" name="name" id="name" value="{{ old('name') }}" required
               placeholder="e.g. Living Room Sensor"
               class="w-full rounded border border-neutral-300 px-3 py-2 text-sm focus:border-black focus:ring-0 focus:outline-none">
         </div>

         <div>
            <label for="device_type" class="block text-xs font-medium text-neutral-600 mb-1">Device Type</label>
            <select name="device_type" id="device_type" required
               class="w-full rounded border border-neutral-300 px-3 py-2 text-sm bg-white focus:border-black focus:ring-0 focus:outline-none">
               <option value="esp32" selected>ESP32</option>
            </select>
         </div>

         <div>
            <label for="device_identifier" class="block text-xs font-medium text-neutral-600 mb-1">Device
               Identifier</label>
            <input type="text" name="device_identifier" id="device_identifier" value="{{ old('device_identifier') }}"
               required placeholder="e.g. ESP32-A1B2C3D4"
               class="w-full rounded border border-neutral-300 px-3 py-2 text-sm font-mono focus:border-black focus:ring-0 focus:outline-none">
            <p class="mt-1 text-[10px] text-neutral-400">Unique hardware identifier (MAC address, serial number, etc.)
            </p>
         </div>

         <div>
            <label for="location" class="block text-xs font-medium text-neutral-600 mb-1">Location <span
                  class="text-neutral-400">(optional)</span></label>
            <input type="text" name="location" id="location" value="{{ old('location') }}"
               placeholder="e.g. Building A, Room 201"
               class="w-full rounded border border-neutral-300 px-3 py-2 text-sm focus:border-black focus:ring-0 focus:outline-none">
         </div>

         <div>
            <label for="description" class="block text-xs font-medium text-neutral-600 mb-1">Description <span
                  class="text-neutral-400">(optional)</span></label>
            <textarea name="description" id="description" rows="3"
               placeholder="Describe what this device monitors or controls..."
               class="w-full rounded border border-neutral-300 px-3 py-2 text-sm focus:border-black focus:ring-0 focus:outline-none">{{ old('description') }}</textarea>
         </div>

         <div class="flex justify-end space-x-2 pt-2">
            <a href="{{ route('devices.index') }}"
               class="px-3 py-2 text-xs font-medium border border-neutral-300 rounded hover:bg-neutral-50 transition">Cancel</a>
            <button type="submit"
               class="px-3 py-2 text-xs font-medium bg-black text-white rounded hover:bg-neutral-800 transition">
               Register Device
            </button>
         </div>
      </form>
   </div>
</div>
@endsection