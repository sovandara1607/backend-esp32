@extends('layouts.app')
@section('title', 'Create Alert Rule — IoT Monitor')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
   <h1 class="text-xl font-semibold text-black">Create Alert Rule</h1>
   <a href="{{ route('alerts.index') }}" class="text-xs text-neutral-400 hover:text-black transition-colors">← Back to
      Alerts</a>
</div>

<div class="max-w-lg">
   <div class="border border-neutral-200 rounded-lg p-6">
      <form method="POST" action="{{ route('alerts.store') }}" class="space-y-5">
         @csrf

         <div>
            <label for="device_id" class="block text-xs font-medium text-neutral-500 mb-1">Device</label>
            <select id="device_id" name="device_id" required
               class="block w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm text-black focus:border-black focus:ring-0 focus:outline-none bg-white">
               <option value="">Select a device</option>
               @foreach($devices as $device)
               <option value="{{ $device->id }}" {{ old('device_id') == $device->id ? 'selected' : '' }}>
                  {{ $device->name }}
               </option>
               @endforeach
            </select>
            @error('device_id') <p class="mt-1 text-xs text-neutral-500">{{ $message }}</p> @enderror
         </div>

         <div>
            <label for="sensor_type" class="block text-xs font-medium text-neutral-500 mb-1">Sensor Type</label>
            <select id="sensor_type" name="sensor_type" required
               class="block w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm text-black focus:border-black focus:ring-0 focus:outline-none bg-white">
               <option value="temperature" {{ old('sensor_type') === 'temperature' ? 'selected' : '' }}>Temperature
               </option>
               <option value="humidity" {{ old('sensor_type') === 'humidity' ? 'selected' : '' }}>Humidity</option>
               <option value="light" {{ old('sensor_type') === 'light' ? 'selected' : '' }}>Light</option>
               <option value="pressure" {{ old('sensor_type') === 'pressure' ? 'selected' : '' }}>Pressure</option>
               <option value="gas" {{ old('sensor_type') === 'gas' ? 'selected' : '' }}>Gas</option>
            </select>
            @error('sensor_type') <p class="mt-1 text-xs text-neutral-500">{{ $message }}</p> @enderror
         </div>

         <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
               <label for="condition" class="block text-xs font-medium text-neutral-500 mb-1">Condition</label>
               <select id="condition" name="condition" required
                  class="block w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm text-black focus:border-black focus:ring-0 focus:outline-none bg-white">
                  <option value=">" {{ old('condition') === '>' ? 'selected' : '' }}>Greater than (>)</option>
                  <option value="<" {{ old('condition') === '<' ? 'selected' : '' }}>Less than (<)< /option>
                  <option value=">=" {{ old('condition') === '>=' ? 'selected' : '' }}>Greater or equal (>=)</option>
                  <option value="<=" {{ old('condition') === '<=' ? 'selected' : '' }}>Less or equal (<=)< /option>
                  <option value="=" {{ old('condition') === '=' ? 'selected' : '' }}>Equal (=)</option>
               </select>
               @error('condition') <p class="mt-1 text-xs text-neutral-500">{{ $message }}</p> @enderror
            </div>

            <div>
               <label for="threshold_value" class="block text-xs font-medium text-neutral-500 mb-1">Threshold</label>
               <input id="threshold_value" type="number" name="threshold_value" step="0.01"
                  value="{{ old('threshold_value') }}" required
                  class="block w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm text-black focus:border-black focus:ring-0 focus:outline-none">
               @error('threshold_value') <p class="mt-1 text-xs text-neutral-500">{{ $message }}</p> @enderror
            </div>
         </div>

         <div>
            <label for="severity" class="block text-xs font-medium text-neutral-500 mb-1">Severity</label>
            <select id="severity" name="severity" required
               class="block w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm text-black focus:border-black focus:ring-0 focus:outline-none bg-white">
               <option value="info" {{ old('severity') === 'info' ? 'selected' : '' }}>Info</option>
               <option value="warning" {{ old('severity', 'warning') === 'warning' ? 'selected' : '' }}>Warning</option>
               <option value="critical" {{ old('severity') === 'critical' ? 'selected' : '' }}>Critical</option>
            </select>
            @error('severity') <p class="mt-1 text-xs text-neutral-500">{{ $message }}</p> @enderror
         </div>

         <div>
            <label for="message" class="block text-xs font-medium text-neutral-500 mb-1">Alert Message</label>
            <input id="message" type="text" name="message" value="{{ old('message') }}" required
               placeholder="e.g. Temperature too high!"
               class="block w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm text-black focus:border-black focus:ring-0 focus:outline-none">
            @error('message') <p class="mt-1 text-xs text-neutral-500">{{ $message }}</p> @enderror
         </div>

         <div class="flex items-center space-x-4 pt-2">
            <button type="submit"
               class="px-4 py-2 bg-black text-white text-sm rounded hover:bg-neutral-800 transition-colors">
               Create Alert Rule
            </button>
            <a href="{{ route('alerts.index') }}" class="text-xs text-neutral-400 hover:text-black">Cancel</a>
         </div>
      </form>
   </div>
</div>
@endsection