@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
{{-- Stats --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
   <div class="border border-neutral-200 rounded-lg p-4">
      <p class="text-xs text-neutral-500 uppercase tracking-wide">Total Devices</p>
      <p class="text-2xl font-bold mt-1">{{ $totalDevices }}</p>
   </div>
   <div class="border border-neutral-200 rounded-lg p-4">
      <p class="text-xs text-neutral-500 uppercase tracking-wide">Online</p>
      <p class="text-2xl font-bold mt-1">{{ $onlineDevices }}</p>
   </div>
   <div class="border border-neutral-200 rounded-lg p-4">
      <p class="text-xs text-neutral-500 uppercase tracking-wide">Offline</p>
      <p class="text-2xl font-bold mt-1">{{ $offlineDevices }}</p>
   </div>
   <div class="border border-neutral-200 rounded-lg p-4">
      <p class="text-xs text-neutral-500 uppercase tracking-wide">Alerts</p>
      <p class="text-2xl font-bold mt-1">{{ $unreadAlertCount }}</p>
   </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
   {{-- Left Column --}}
   <div class="lg:col-span-2 space-y-6">

      {{-- Sensor Chart --}}
      <div class="border border-neutral-200 rounded-lg p-5">
         <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
            <h3 class="text-sm font-semibold">Sensor Data</h3>
            <div class="flex flex-wrap gap-2">
               <select id="chart-device" class="text-xs border border-neutral-300 rounded px-2 py-1 bg-white"
                  onchange="loadChartData()">
                  @foreach($devices as $device)
                  <option value="{{ $device->id }}">{{ $device->name }}</option>
                  @endforeach
                  @if($devices->isEmpty())
                  <option value="">No devices</option>
                  @endif
               </select>
               <select id="chart-sensor" class="text-xs border border-neutral-300 rounded px-2 py-1 bg-white"
                  onchange="loadChartData()">
                  <option value="temperature">Temperature</option>
                  <option value="humidity">Humidity</option>
                  <!-- <option value="light">Light</option>
                  <option value="pressure">Pressure</option>
                  <option value="gas">Gas</option> -->
               </select>
               <select id="chart-hours" class="text-xs border border-neutral-300 rounded px-2 py-1 bg-white"
                  onchange="loadChartData()">
                  <option value="1">1h</option>
                  <option value="6">6h</option>
                  <option value="24" selected>24h</option>
                  <option value="168">7d</option>
               </select>
            </div>
         </div>
         <div class="h-56">
            <canvas id="sensorChart"></canvas>
         </div>
         <p id="chart-empty" class="text-center text-neutral-400 text-xs mt-3 hidden">No sensor data available.</p>
      </div>

      {{-- Fan Control --}}
      <div class="border border-neutral-200 rounded-lg p-5">
         <h3 class="text-sm font-semibold mb-4">Fan Control (ESP32)</h3>
         <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div class="flex items-center space-x-4">
               <svg id="fan-icon" class="w-12 h-12 text-neutral-300 transition-all duration-300"
                  xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                  stroke-width="1.5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 12c0-2.5-2-4.5-4.5-4.5S3 7 3 9s2.5 3 4.5 3H12z
                     M12 12c2.5 0 4.5-2 4.5-4.5S17 3 15 3s-3 2.5-3 4.5V12z
                     M12 12c0 2.5 2 4.5 4.5 4.5S21 17 21 15s-2.5-3-4.5-3H12z
                     M12 12c-2.5 0-4.5 2-4.5 4.5S7 21 9 21s3-2.5 3-4.5V12z" />
                  <circle cx="12" cy="12" r="1.5" fill="currentColor" />
               </svg>
               <div>
                  <p class="text-xs text-neutral-500">Status</p>
                  <p id="fan-status" class="text-lg font-bold text-neutral-300">--</p>
               </div>
            </div>
            <button id="fan-toggle" onclick="toggleFan()" disabled
               class="px-5 py-2 rounded text-sm font-medium bg-neutral-200 text-neutral-500 cursor-not-allowed transition">
               Loading...
            </button>
         </div>
         <div id="speed-section" class="mt-4 pt-3 border-t border-neutral-100 hidden">
            <div class="flex items-center justify-between mb-1.5">
               <label class="text-xs text-neutral-500">Speed</label>
               <span id="speed-value" class="text-xs font-semibold">255</span>
            </div>
            <input id="speed-slider" type="range" min="0" max="255" value="255"
               class="w-full h-1.5 bg-neutral-200 rounded-lg appearance-none cursor-pointer accent-black"
               oninput="onSpeedChange(this.value)">
         </div>
      </div>

      {{-- Voice Commands / Sinric Pro --}}
      <div id="voice-commands" class="border border-neutral-200 rounded-lg p-5">
         <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-4">
            <h3 class="text-sm font-semibold">Voice Control</h3>
            <div class="flex items-center space-x-2">
               <span
                  class="inline-flex items-center text-[10px] font-medium border border-neutral-300 rounded px-2 py-0.5">Google
                  Assistant</span>
               <span
                  class="inline-flex items-center text-[10px] font-medium border border-neutral-300 rounded px-2 py-0.5">Sinric
                  Pro</span>
            </div>
         </div>

         {{-- Status --}}
         <div class="flex items-center space-x-3 mb-4 p-3 bg-neutral-50 rounded">
            <div id="sinric-status-dot" class="w-2 h-2 rounded-full bg-neutral-300"></div>
            <span id="sinric-status-text" class="text-xs text-neutral-500">Checking Sinric Pro connection...</span>
            <button onclick="checkSinricStatus()"
               class="ml-auto text-[10px] underline text-neutral-400 hover:text-black">Refresh</button>
         </div>

         {{-- Speak Command --}}
         <div class="mb-4 p-3 border border-neutral-200 rounded-lg">
            <div class="flex items-center gap-3">
               <button id="dash-mic-btn" onclick="toggleDashSpeech()"
                  class="relative flex-shrink-0 w-10 h-10 rounded-full border-2 border-neutral-300 bg-white flex items-center justify-center transition-all duration-200 hover:border-black focus:outline-none"
                  title="Click to speak a command">
                  <div id="dash-mic-pulse"
                     class="absolute inset-0 rounded-full bg-black animate-ping opacity-0 pointer-events-none"></div>
                  <svg id="dash-mic-icon" class="w-5 h-5 text-neutral-400 relative z-10" fill="none" viewBox="0 0 24 24"
                     stroke="currentColor" stroke-width="2">
                     <path stroke-linecap="round" stroke-linejoin="round"
                        d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                  </svg>
               </button>
               <div class="flex-1 min-w-0">
                  <p id="dash-mic-status" class="text-xs text-neutral-500">Click the mic to speak a command</p>
                  <p id="dash-mic-transcript" class="text-xs text-black font-medium truncate mt-0.5"></p>
               </div>
               <label class="flex items-center gap-1.5 cursor-pointer flex-shrink-0">
                  <span class="text-[10px] text-neutral-400">Hands-free</span>
                  <input type="checkbox" id="dash-handsfree" onchange="toggleDashHandsFree()" class="sr-only peer">
                  <div
                     class="w-7 h-4 bg-neutral-200 peer-checked:bg-black rounded-full relative transition-colors after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:w-3 after:h-3 after:bg-white after:rounded-full after:transition-all peer-checked:after:translate-x-3">
                  </div>
               </label>
            </div>
            <p id="dash-mic-unsupported" class="hidden text-[10px] text-neutral-400 mt-2">Your browser doesn't support
               speech recognition. Try Chrome or Edge.</p>
         </div>

         {{-- Sample Voice Commands --}}
         <div class="mb-4">
            <p class="text-xs text-neutral-500 mb-2">Try these voice commands with Google Assistant:</p>
            <div class="space-y-1.5">
               <div
                  class="flex items-center justify-between p-2.5 border border-neutral-100 rounded group hover:border-neutral-300 transition cursor-pointer"
                  onclick="executeVoiceCommand('turn_on', 'fan')">
                  <div class="flex items-center space-x-2.5">
                     <svg class="w-3.5 h-3.5 text-neutral-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                           d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                     </svg>
                     <span class="text-xs">"Hey Gout, turn on the fan"</span>
                  </div>
                  <span
                     class="text-[10px] text-neutral-400 hidden sm:inline sm:opacity-0 sm:group-hover:opacity-100 transition">Click
                     to
                     simulate</span>
               </div>
               <div
                  class="flex items-center justify-between p-2.5 border border-neutral-100 rounded group hover:border-neutral-300 transition cursor-pointer"
                  onclick="executeVoiceCommand('turn_off', 'fan')">
                  <div class="flex items-center space-x-2.5">
                     <svg class="w-3.5 h-3.5 text-neutral-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                           d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                     </svg>
                     <span class="text-xs">"Hey Gout, turn off the fan"</span>
                  </div>
                  <span
                     class="text-[10px] text-neutral-400 hidden sm:inline sm:opacity-0 sm:group-hover:opacity-100 transition">Click
                     to
                     simulate</span>
               </div>
               <div
                  class="flex items-center justify-between p-2.5 border border-neutral-100 rounded group hover:border-neutral-300 transition cursor-pointer"
                  onclick="executeVoiceCommand('set_speed', 'fan', 50)">
                  <div class="flex items-center space-x-2.5">
                     <svg class="w-3.5 h-3.5 text-neutral-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                           d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                     </svg>
                     <span class="text-xs">"Hey Gout, set the fan to 50 percent"</span>
                  </div>
                  <span
                     class="text-[10px] text-neutral-400 hidden sm:inline sm:opacity-0 sm:group-hover:opacity-100 transition">Click
                     to
                     simulate</span>
               </div>
               <div
                  class="flex items-center justify-between p-2.5 border border-neutral-100 rounded group hover:border-neutral-300 transition cursor-pointer"
                  onclick="executeVoiceCommand('get_temperature', 'sensor')">
                  <div class="flex items-center space-x-2.5">
                     <svg class="w-3.5 h-3.5 text-neutral-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                           d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                     </svg>
                     <span class="text-xs">"Hey Gout, what's the temperature?"</span>
                  </div>
                  <span
                     class="text-[10px] text-neutral-400 hidden sm:inline sm:opacity-0 sm:group-hover:opacity-100 transition">Click
                     to
                     simulate</span>
               </div>
               <div
                  class="flex items-center justify-between p-2.5 border border-neutral-100 rounded group hover:border-neutral-300 transition cursor-pointer"
                  onclick="executeVoiceCommand('get_humidity', 'sensor')">
                  <div class="flex items-center space-x-2.5">
                     <svg class="w-3.5 h-3.5 text-neutral-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                           d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                     </svg>
                     <span class="text-xs">"Hey Gout, what's the humidity?"</span>
                  </div>
                  <span
                     class="text-[10px] text-neutral-400 hidden sm:inline sm:opacity-0 sm:group-hover:opacity-100 transition">Click
                     to
                     simulate</span>
               </div>
               <div
                  class="flex items-center justify-between p-2.5 border border-neutral-100 rounded group hover:border-neutral-300 transition cursor-pointer"
                  onclick="executeVoiceCommand('toggle_all', 'devices')">
                  <div class="flex items-center space-x-2.5">
                     <svg class="w-3.5 h-3.5 text-neutral-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                           d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                     </svg>
                     <span class="text-xs">"Hey Gout, turn off all devices"</span>
                  </div>
                  <span
                     class="text-[10px] text-neutral-400 hidden sm:inline sm:opacity-0 sm:group-hover:opacity-100 transition">Click
                     to
                     simulate</span>
               </div>
            </div>
         </div>

         {{-- Command Log --}}
         <div>
            <div class="flex items-center justify-between mb-2">
               <p class="text-xs text-neutral-500">Command Log</p>
               <button onclick="clearCommandLog()"
                  class="text-[10px] underline text-neutral-400 hover:text-black">Clear</button>
            </div>
            <div id="voice-command-log"
               class="bg-neutral-50 rounded p-3 max-h-40 overflow-y-auto font-mono text-[11px] text-neutral-600 space-y-1">
               <p class="text-neutral-400 italic">No commands executed yet.</p>
            </div>
         </div>

         {{-- Sinric Pro Setup Info --}}
         <details class="mt-4">
            <summary class="text-xs text-neutral-500 cursor-pointer hover:text-black">Sinric Pro Setup Guide</summary>
            <div class="mt-2 p-3 bg-neutral-50 rounded text-[11px] text-neutral-600 space-y-2">
               <p><strong>1.</strong> Create a Sinric Pro account at <span class="font-mono">sinric.pro</span></p>
               <p><strong>2.</strong> Add your ESP32 device with type <span class="font-mono">Fan</span> or <span
                     class="font-mono">Temperature Sensor</span></p>
               <p><strong>3.</strong> Copy your App Key and App Secret to your <span class="font-mono">.env</span> file:
               </p>
               <pre class="bg-white border border-neutral-200 rounded p-2 overflow-x-auto">SINRIC_APP_KEY=your-app-key
SINRIC_APP_SECRET=your-app-secret
SINRIC_DEVICE_ID=your-device-id</pre>
               <p><strong>4.</strong> Link Sinric Pro to Google Home app under <strong>Works with Google</strong></p>
               <p><strong>5.</strong> Upload the ESP32 code with the Sinric Pro library included</p>
               <p class="border-t border-neutral-200 pt-2 mt-2"><strong>API Endpoint:</strong> <span
                     class="font-mono">POST /api/sinric/callback</span> receives commands from Sinric Pro and forwards
                  them to your ESP32 via this dashboard.</p>
            </div>
         </details>
      </div>

      {{-- Temperature Control --}}
      <div class="border border-neutral-200 rounded-lg p-5">
         <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold">Temperature Control</h3>
            <div class="flex items-center space-x-2">
               @if($tempControlActive && $activeProfile)
                  <span class="w-2 h-2 rounded-full bg-black"></span>
                  <span class="text-[10px] text-black font-medium">{{ $activeProfile->name }}</span>
               @else
                  <span class="w-2 h-2 rounded-full bg-neutral-300"></span>
                  <span class="text-[10px] text-neutral-400">Manual Mode</span>
               @endif
            </div>
         </div>

         @if($tempControlActive && $activeProfile)
            <div class="space-y-3">
               <div class="p-3 bg-neutral-50 rounded">
                  <p class="text-xs text-neutral-500 mb-2">Active Profile: <span class="font-semibold text-black">{{ $activeProfile->name }}</span></p>
                  <div class="space-y-1">
                     @foreach($activeProfile->rules->sortBy('temperature') as $rule)
                        <p class="text-[11px] text-neutral-500 font-mono">>= {{ $rule->temperature }}C &rarr; Fan {{ $rule->fan_speed_percent }}%</p>
                     @endforeach
                  </div>
               </div>
               <form method="POST" action="{{ route('temperature-control.deactivate') }}">
                  @csrf
                  <button type="submit"
                     class="w-full px-4 py-2 border border-black text-black text-xs rounded hover:bg-neutral-50 transition">
                     Deactivate &mdash; Switch to Manual
                  </button>
               </form>
            </div>
         @else
            @if($temperatureProfiles->count() > 0)
               <div class="space-y-2">
                  @foreach($temperatureProfiles as $profile)
                     <div class="flex items-center justify-between p-2.5 border border-neutral-100 rounded hover:border-neutral-300 transition">
                        <div>
                           <p class="text-xs font-medium">{{ $profile->name }}</p>
                           <p class="text-[10px] text-neutral-400">{{ $profile->rules->count() }} rule{{ $profile->rules->count() !== 1 ? 's' : '' }}</p>
                        </div>
                        @if($profile->rules->count() > 0)
                           <form method="POST" action="{{ route('temperature-control.activate', $profile) }}">
                              @csrf
                              <button type="submit"
                                 class="px-3 py-1 bg-black text-white text-xs rounded hover:bg-neutral-800 transition">
                                 Activate
                              </button>
                           </form>
                        @else
                           <span class="text-[10px] text-neutral-400">No rules</span>
                        @endif
                     </div>
                  @endforeach
               </div>
            @else
               <p class="text-xs text-neutral-400 text-center py-3">No temperature profiles yet.</p>
            @endif
         @endif

         <div class="mt-3 pt-3 border-t border-neutral-100">
            <a href="{{ route('temperature-control') }}"
               class="text-xs text-neutral-500 hover:text-black underline underline-offset-2">
               Manage profiles &amp; rules &rarr;
            </a>
         </div>
      </div>

      {{-- Device List --}}
      <div class="border border-neutral-200 rounded-lg p-5">
         <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold">Your Devices</h3>
            <a href="{{ route('devices.create') }}" class="text-xs font-medium text-neutral-500 hover:text-black">+ Add
               Device</a>
         </div>
         @forelse($devices as $device)
         <div class="flex items-center justify-between py-2.5 {{ !$loop->last ? 'border-b border-neutral-100' : '' }}">
            <div class="flex items-center space-x-2.5">
               <div class="w-1.5 h-1.5 rounded-full {{ $device->status === 'online' ? 'bg-black' : 'bg-neutral-300' }}">
               </div>
               <div>
                  <a href="{{ route('devices.show', $device) }}"
                     class="text-xs font-medium hover:underline">{{ $device->name }}</a>
                  <p class="text-[10px] text-neutral-400">{{ $device->device_type }} &middot;
                     {{ $device->device_identifier }}
                  </p>
               </div>
            </div>
            <div class="text-right">
               <span
                  class="text-[10px] font-medium px-2 py-0.5 rounded border {{ $device->status === 'online' ? 'border-black text-black' : 'border-neutral-300 text-neutral-400' }}">
                  {{ ucfirst($device->status) }}
               </span>
            </div>
         </div>
         @empty
         <div class="text-center py-6">
            <p class="text-xs text-neutral-400">No devices yet</p>
            <a href="{{ route('devices.create') }}"
               class="mt-2 inline-block text-xs underline text-neutral-500 hover:text-black">Register your first
               device</a>
         </div>
         @endforelse
      </div>
   </div>

   {{-- Right Column --}}
   <div class="space-y-6">

      {{-- Alerts --}}
      <div id="alerts" class="border border-neutral-200 rounded-lg p-5">
         <h3 class="text-sm font-semibold mb-3">Recent Alerts</h3>
         @forelse($unreadAlerts as $alert)
         <div class="flex items-start space-x-2.5 py-2 {{ !$loop->last ? 'border-b border-neutral-100' : '' }}">
            <div class="mt-1">
               <span
                  class="flex h-1.5 w-1.5 rounded-full {{ $alert->severity === 'critical' ? 'bg-black' : 'bg-neutral-400' }}"></span>
            </div>
            <div class="flex-1 min-w-0">
               <p class="text-xs">{{ $alert->message }}</p>
               <p class="text-[10px] text-neutral-400 mt-0.5">
                  {{ $alert->triggered_at ? $alert->triggered_at->diffForHumans() : $alert->created_at->diffForHumans() }}
               </p>
            </div>
         </div>
         @empty
         <p class="text-xs text-neutral-400 text-center py-4">No active alerts</p>
         @endforelse
      </div>

      {{-- Recent Readings --}}
      <div class="border border-neutral-200 rounded-lg p-5">
         <h3 class="text-sm font-semibold mb-3">Recent Readings</h3>
         @forelse($recentSensorData as $reading)
         <div class="flex items-center justify-between py-2 {{ !$loop->last ? 'border-b border-neutral-50' : '' }}">
            <div>
               <p class="text-xs font-medium">{{ ucfirst($reading->sensor_type) }}</p>
               <p class="text-[10px] text-neutral-400">{{ $reading->device->name }}</p>
            </div>
            <div class="text-right">
               <p class="text-xs font-semibold">{{ $reading->value }} {{ $reading->unit }}</p>
               <p class="text-[10px] text-neutral-400">{{ $reading->recorded_at->diffForHumans() }}</p>
            </div>
         </div>
         @empty
         <p class="text-xs text-neutral-400 text-center py-4">No readings yet</p>
         @endforelse
      </div>

      {{-- Sinric Pro Quick Actions --}}
      <div class="border border-neutral-200 rounded-lg p-5">
         <h3 class="text-sm font-semibold mb-3">Quick Actions</h3>
         <div class="space-y-2">
            <button onclick="executeVoiceCommand('turn_on', 'fan')"
               class="w-full text-left flex items-center justify-between px-3 py-2 border border-neutral-200 rounded hover:border-black transition text-xs">
               <span>Turn Fan On</span>
               <svg class="w-3 h-3 text-neutral-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                  stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
               </svg>
            </button>
            <button onclick="executeVoiceCommand('turn_off', 'fan')"
               class="w-full text-left flex items-center justify-between px-3 py-2 border border-neutral-200 rounded hover:border-black transition text-xs">
               <span>Turn Fan Off</span>
               <svg class="w-3 h-3 text-neutral-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                  stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
               </svg>
            </button>
            <button onclick="executeVoiceCommand('get_temperature', 'sensor')"
               class="w-full text-left flex items-center justify-between px-3 py-2 border border-neutral-200 rounded hover:border-black transition text-xs">
               <span>Get Temperature</span>
               <svg class="w-3 h-3 text-neutral-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                  stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
               </svg>
            </button>
            <button onclick="executeVoiceCommand('get_humidity', 'sensor')"
               class="w-full text-left flex items-center justify-between px-3 py-2 border border-neutral-200 rounded hover:border-black transition text-xs">
               <span>Get Humidity</span>
               <svg class="w-3 h-3 text-neutral-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                  stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
               </svg>
            </button>
         </div>
      </div>
   </div>
</div>
@endsection

@push('scripts')
<script>
let currentState = null;
let currentSpeed = 255;
let speedTimeout = null;
let sensorChart = null;

// ─── Fan Control ───
async function fetchFanStatus() {
   try {
      const res = await axios.get('/api/fan/status');
      updateFanUI(res.data.status, res.data.speed);
   } catch (e) {
      document.getElementById('fan-status').textContent = 'Error';
   }
}

function updateFanUI(status, speed) {
   currentState = status;
   if (speed !== undefined) currentSpeed = speed;
   const isOn = status === 'on';

   document.getElementById('fan-status').textContent = isOn ? 'ON' : 'OFF';
   document.getElementById('fan-status').className = 'text-lg font-bold ' + (isOn ? 'text-black' : 'text-neutral-300');

   const btn = document.getElementById('fan-toggle');
   btn.disabled = false;
   btn.className = 'px-5 py-2 rounded text-sm font-medium transition cursor-pointer ' +
      (isOn ? 'bg-black text-white hover:bg-neutral-800' : 'border border-black text-black hover:bg-neutral-50');
   btn.textContent = isOn ? 'Turn Off' : 'Turn On';

   document.getElementById('fan-icon').className = 'w-12 h-12 transition-all duration-300 ' +
      (isOn ? 'text-black animate-spin' : 'text-neutral-300');

   document.getElementById('speed-section').classList.toggle('hidden', !isOn);
   document.getElementById('speed-slider').value = currentSpeed;
   document.getElementById('speed-value').textContent = currentSpeed;
}

function onSpeedChange(value) {
   document.getElementById('speed-value').textContent = value;
   clearTimeout(speedTimeout);
   speedTimeout = setTimeout(() => axios.get('/api/fan/speed/' + value), 300);
}

async function toggleFan() {
   const btn = document.getElementById('fan-toggle');
   btn.disabled = true;
   btn.textContent = 'Sending...';
   try {
      await axios.get(currentState === 'on' ? '/api/fan/off' : '/api/fan/on');
      await fetchFanStatus();
   } catch (e) {
      btn.textContent = 'Error';
      btn.disabled = false;
   }
}

// ─── Sensor Chart ───
async function loadChartData() {
   const deviceId = document.getElementById('chart-device').value;
   const sensorType = document.getElementById('chart-sensor').value;
   const hours = document.getElementById('chart-hours').value;

   if (!deviceId) {
      document.getElementById('chart-empty').classList.remove('hidden');
      return;
   }

   try {
      const res = await axios.get(`/api/devices/${deviceId}/sensor-data/chart`, {
         params: {
            sensor_type: sensorType,
            hours: hours
         }
      });

      const labels = res.data.labels;
      const values = res.data.values;

      if (!labels.length) {
         document.getElementById('chart-empty').classList.remove('hidden');
         if (sensorChart) sensorChart.destroy();
         return;
      }

      document.getElementById('chart-empty').classList.add('hidden');
      if (sensorChart) sensorChart.destroy();

      const ctx = document.getElementById('sensorChart').getContext('2d');
      sensorChart = new Chart(ctx, {
         type: 'line',
         data: {
            labels: labels,
            datasets: [{
               label: sensorType.charAt(0).toUpperCase() + sensorType.slice(1),
               data: values,
               borderColor: '#000000',
               backgroundColor: 'rgba(0, 0, 0, 0.05)',
               fill: true,
               tension: 0.4,
               pointRadius: 1.5,
               pointHoverRadius: 4,
               borderWidth: 1.5,
            }]
         },
         options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
               legend: {
                  display: false
               }
            },
            scales: {
               x: {
                  grid: {
                     display: false
                  },
                  ticks: {
                     maxTicksLimit: 12,
                     font: {
                        size: 10
                     },
                     color: '#a3a3a3'
                  },
                  border: {
                     color: '#e5e5e5'
                  },
               },
               y: {
                  grid: {
                     color: '#f5f5f5'
                  },
                  ticks: {
                     font: {
                        size: 10
                     },
                     color: '#a3a3a3'
                  },
                  border: {
                     color: '#e5e5e5'
                  },
               }
            }
         }
      });
   } catch (e) {
      document.getElementById('chart-empty').classList.remove('hidden');
   }
}

// ─── Dashboard Speech Recognition ───
let dashRecognition = null;
let dashListening = false;
let dashHandsFree = false;
let dashSynth = window.speechSynthesis;

function initDashSpeech() {
   const SR = window.SpeechRecognition || window.webkitSpeechRecognition;
   if (!SR) {
      document.getElementById('dash-mic-unsupported').classList.remove('hidden');
      document.getElementById('dash-mic-btn').disabled = true;
      document.getElementById('dash-mic-btn').classList.add('opacity-40', 'cursor-not-allowed');
      return;
   }
   dashRecognition = new SR();
   dashRecognition.continuous = false;
   dashRecognition.interimResults = true;
   dashRecognition.lang = 'en-US';

   dashRecognition.onstart = () => {
      dashListening = true;
      updateDashMicUI(true);
      document.getElementById('dash-mic-status').textContent = 'Listening...';
      document.getElementById('dash-mic-status').className = 'text-xs text-black font-medium';
   };

   dashRecognition.onresult = (event) => {
      let interim = '',
         final = '';
      for (let i = event.resultIndex; i < event.results.length; i++) {
         const t = event.results[i][0].transcript;
         if (event.results[i].isFinal) final += t;
         else interim += t;
      }
      document.getElementById('dash-mic-transcript').textContent = `"${final || interim}"`;
      if (final) dashProcessVoice(final.trim().toLowerCase());
   };

   dashRecognition.onerror = (event) => {
      if (event.error === 'no-speech') {
         document.getElementById('dash-mic-status').textContent = 'No speech detected.';
      } else if (event.error === 'not-allowed') {
         document.getElementById('dash-mic-status').textContent = 'Mic access denied.';
      }
      dashListening = false;
      updateDashMicUI(false);
   };

   dashRecognition.onend = () => {
      dashListening = false;
      updateDashMicUI(false);
      if (dashHandsFree) {
         setTimeout(() => {
            if (dashHandsFree && !dashListening) {
               try {
                  dashRecognition.start();
               } catch (e) {}
            }
         }, 500);
         document.getElementById('dash-mic-status').textContent = 'Hands-free — say "Hey Fan" + command';
      } else {
         document.getElementById('dash-mic-status').textContent = 'Click the mic to speak a command';
         document.getElementById('dash-mic-status').className = 'text-xs text-neutral-500';
      }
   };
}

function toggleDashSpeech() {
   if (!dashRecognition) return;
   if (dashListening) {
      dashRecognition.abort();
   } else {
      document.getElementById('dash-mic-transcript').textContent = '';
      try {
         dashRecognition.start();
      } catch (e) {}
   }
}

function toggleDashHandsFree() {
   dashHandsFree = document.getElementById('dash-handsfree').checked;
   if (dashHandsFree) {
      if (!dashRecognition) initDashSpeech();
      dashRecognition.continuous = true;
      document.getElementById('dash-mic-status').textContent = 'Hands-free — say "Hey Fan" + command';
      if (!dashListening) {
         try {
            dashRecognition.start();
         } catch (e) {}
      }
      logCommand('Hands-free mode enabled', 'info');
   } else {
      if (dashRecognition) {
         dashRecognition.continuous = false;
         if (dashListening) dashRecognition.abort();
      }
      document.getElementById('dash-mic-status').textContent = 'Click the mic to speak a command';
      logCommand('Hands-free mode disabled', 'info');
   }
}

function updateDashMicUI(active) {
   const btn = document.getElementById('dash-mic-btn');
   const icon = document.getElementById('dash-mic-icon');
   const pulse = document.getElementById('dash-mic-pulse');
   if (active) {
      btn.classList.remove('border-neutral-300', 'bg-white');
      btn.classList.add('border-black', 'bg-black');
      icon.classList.remove('text-neutral-400');
      icon.classList.add('text-white');
      pulse.classList.remove('opacity-0');
      pulse.classList.add('opacity-75');
   } else {
      btn.classList.remove('border-black', 'bg-black');
      btn.classList.add('border-neutral-300', 'bg-white');
      icon.classList.remove('text-white');
      icon.classList.add('text-neutral-400');
      pulse.classList.remove('opacity-75');
      pulse.classList.add('opacity-0');
   }
}

function dashProcessVoice(text) {
   const wakeWords = ['hey fan', 'hey fans', 'a fan', 'hey van', 'hey fam'];
   let cmd = text;
   for (const w of wakeWords) {
      if (cmd.startsWith(w)) {
         cmd = cmd.substring(w.length).trim();
         break;
      }
   }
   if (!cmd || cmd.length < 2) return;

   logCommand(`Voice: "${text}"`, 'info');

   if (/turn\s*on.*fan|fan\s*on|start.*fan|enable.*fan/.test(cmd)) {
      executeVoiceCommand('turn_on', 'fan');
   } else if (/turn\s*off.*fan|fan\s*off|stop.*fan|disable.*fan|shut.*off/.test(cmd)) {
      executeVoiceCommand('turn_off', 'fan');
   } else if (/set.*speed|speed.*to|fan.*(\d+)\s*percent/.test(cmd)) {
      const m = cmd.match(/(\d+)/);
      executeVoiceCommand('set_speed', 'fan', m ? Math.min(parseInt(m[1]), 100) : 50);
   } else if (/temperature|how hot|temp/.test(cmd)) {
      executeVoiceCommand('get_temperature', 'sensor');
   } else if (/humidity|how humid/.test(cmd)) {
      executeVoiceCommand('get_humidity', 'sensor');
   } else if (/turn\s*on|start|enable/.test(cmd)) {
      executeVoiceCommand('turn_on', 'fan');
   } else if (/turn\s*off|stop|disable|shut/.test(cmd)) {
      executeVoiceCommand('turn_off', 'fan');
   } else {
      logCommand(`Unrecognized: "${cmd}"`, 'error');
   }
}

// ─── Voice Commands / Sinric Pro ───
function logCommand(message, type = 'info') {
   const log = document.getElementById('voice-command-log');
   if (log.querySelector('.italic')) log.innerHTML = '';

   const time = new Date().toLocaleTimeString('en-US', {
      hour12: false,
      hour: '2-digit',
      minute: '2-digit',
      second: '2-digit'
   });
   const prefix = type === 'success' ? '✓' : type === 'error' ? '✗' : '→';
   const el = document.createElement('p');
   el.textContent = `[${time}] ${prefix} ${message}`;
   if (type === 'error') el.classList.add('text-neutral-900', 'font-semibold');
   log.appendChild(el);
   log.scrollTop = log.scrollHeight;
}

function clearCommandLog() {
   document.getElementById('voice-command-log').innerHTML =
      '<p class="text-neutral-400 italic">No commands executed yet.</p>';
}

async function checkSinricStatus() {
   const dot = document.getElementById('sinric-status-dot');
   const text = document.getElementById('sinric-status-text');
   text.textContent = 'Checking...';

   try {
      const res = await axios.get('/api/sinric/status');
      if (res.data.connected) {
         dot.className = 'w-2 h-2 rounded-full bg-black';
         text.textContent = 'Sinric Pro connected — Device ID: ' + (res.data.device_id || 'N/A');
      } else {
         dot.className = 'w-2 h-2 rounded-full bg-neutral-300';
         text.textContent = 'Sinric Pro not configured. See setup guide below.';
      }
   } catch (e) {
      dot.className = 'w-2 h-2 rounded-full bg-neutral-300';
      text.textContent = 'Sinric Pro not configured. See setup guide below.';
   }
}

async function executeVoiceCommand(action, device, value = null) {
   const cmdMap = {
      'turn_on': `Turn on ${device}`,
      'turn_off': `Turn off ${device}`,
      'set_speed': `Set ${device} speed to ${value}%`,
      'get_temperature': 'Get current temperature',
      'get_humidity': 'Get current humidity',
      'toggle_all': 'Toggle all devices',
   };
   const description = cmdMap[action] || `${action} ${device}`;
   logCommand(`Executing: ${description}`);

   try {
      const res = await axios.post('/api/sinric/command', {
         action: action,
         device: device,
         value: value,
      });

      if (res.data.success) {
         logCommand(res.data.message || `${description} — OK`, 'success');
         // Refresh fan status if it was a fan command
         if (device === 'fan') {
            setTimeout(fetchFanStatus, 500);
         }
      } else {
         logCommand(res.data.message || `Failed: ${description}`, 'error');
      }
   } catch (e) {
      const msg = e.response?.data?.message || e.message || 'Request failed';
      logCommand(`Error: ${msg}`, 'error');
   }
}

// ─── Init ───
initDashSpeech();
fetchFanStatus();
setInterval(fetchFanStatus, 3000);
loadChartData();
setInterval(loadChartData, 30000);
checkSinricStatus();
</script>
@endpush