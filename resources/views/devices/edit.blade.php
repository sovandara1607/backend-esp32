@extends('layouts.app')
@section('title', 'Edit ' . $device->name)

@section('content')
<div class="max-w-lg mx-auto">
   <div class="mb-5">
      <a href="{{ route('devices.show', $device) }}" class="text-xs text-neutral-400 hover:text-black">&larr; Back to {{ $device->name }}</a>
      <h2 class="mt-1.5 text-lg font-semibold">Edit Device</h2>
   </div>

   <div class="border border-neutral-200 rounded-lg p-5">
      <form method="POST" action="{{ route('devices.update', $device) }}" class="space-y-4">
         @csrf
         @method('PUT')

         <div>
            <label for="name" class="block text-xs font-medium text-neutral-600 mb-1">Device Name</label>
            <input type="text" name="name" id="name" value="{{ old('name', $device->name) }}" required
               class="w-full rounded border border-neutral-300 px-3 py-2 text-sm focus:border-black focus:ring-0 focus:outline-none">
         </div>

         <div>
            <label for="device_type" class="block text-xs font-medium text-neutral-600 mb-1">Device Type</label>
            <select name="device_type" id="device_type" required
               class="w-full rounded border border-neutral-300 px-3 py-2 text-sm bg-white focus:border-black focus:ring-0 focus:outline-none">
               @foreach(['esp32', 'esp8266', 'arduino', 'raspberry_pi', 'other'] as $type)
               <option value="{{ $type }}" {{ old('device_type', $device->device_type) === $type ? 'selected' : '' }}>
                  {{ strtoupper($type) }}
               </option>
               @endforeach
            </select>
         </div>

         <div>
            <label class="block text-xs font-medium text-neutral-600 mb-1">Device Identifier</label>
            <input type="text" value="{{ $device->device_identifier }}" disabled
               class="w-full rounded border border-neutral-200 bg-neutral-50 text-neutral-400 px-3 py-2 text-sm font-mono cursor-not-allowed">
            <p class="mt-1 text-[10px] text-neutral-400">Identifier cannot be changed after registration.</p>
         </div>

         <div>
            <label for="location" class="block text-xs font-medium text-neutral-600 mb-1">Location</label>
            <input type="text" name="location" id="location" value="{{ old('location', $device->location) }}"
               class="w-full rounded border border-neutral-300 px-3 py-2 text-sm focus:border-black focus:ring-0 focus:outline-none">
         </div>

         <div>
            <label for="description" class="block text-xs font-medium text-neutral-600 mb-1">Description</label>
            <textarea name="description" id="description" rows="3"
               class="w-full rounded border border-neutral-300 px-3 py-2 text-sm focus:border-black focus:ring-0 focus:outline-none">{{ old('description', $device->description) }}</textarea>
         </div>

         <div class="flex items-center">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" id="is_active" value="1"
               {{ old('is_active', $device->is_active) ? 'checked' : '' }}
               class="rounded border-neutral-300 text-black focus:ring-0">
            <label for="is_active" class="ml-2 text-xs text-neutral-600">Device is active</label>
         </div>

         <div class="flex justify-end space-x-2 pt-2">
            <a href="{{ route('devices.show', $device) }}"
               class="px-3 py-2 text-xs font-medium border border-neutral-300 rounded hover:bg-neutral-50 transition">Cancel</a>
            <button type="submit"
               class="px-3 py-2 text-xs font-medium bg-black text-white rounded hover:bg-neutral-800 transition">
               Save Changes
            </button>
         </div>
      </form>
   </div>
</div>
@endsection