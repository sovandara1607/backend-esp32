@extends('layouts.app')
@section('title', 'Temperature Control')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
   <h1 class="text-xl font-semibold text-black">Temperature Control</h1>
   <div class="flex items-center space-x-2">
      @if($tempControlActive && $activeProfile)
         <span class="w-2 h-2 rounded-full bg-black"></span>
         <span class="text-[10px] text-black font-medium">Active: {{ $activeProfile->name }}</span>
      @else
         <span class="w-2 h-2 rounded-full bg-neutral-300"></span>
         <span class="text-[10px] text-neutral-400">Manual Mode</span>
      @endif
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

      {{-- Active Status --}}
      <div class="border border-neutral-200 rounded-lg p-5">
         <h3 class="text-sm font-semibold mb-3">Temperature Control Status</h3>
         @if($tempControlActive && $activeProfile)
            <div class="flex items-center justify-between">
               <div>
                  <p class="text-xs text-neutral-500">Active Profile</p>
                  <p class="text-sm font-semibold mt-0.5">{{ $activeProfile->name }}</p>
                  <div class="mt-2 space-y-1">
                     @foreach($activeProfile->rules->sortBy('temperature') as $rule)
                        <p class="text-xs text-neutral-500">>= {{ $rule->temperature }}C &rarr; Fan {{ $rule->fan_speed_percent }}%</p>
                     @endforeach
                  </div>
               </div>
               <form method="POST" action="{{ route('temperature-control.deactivate') }}">
                  @csrf
                  <button type="submit"
                     class="px-4 py-2 border border-black text-black text-xs rounded hover:bg-neutral-50 transition">
                     Deactivate
                  </button>
               </form>
            </div>
         @else
            <p class="text-xs text-neutral-400">No temperature profile is active. Fan is in manual mode.</p>
            <p class="text-xs text-neutral-400 mt-1">Create a profile below and activate it to enable automatic fan speed control.</p>
         @endif
      </div>

      {{-- Create Profile --}}
      <div class="border border-neutral-200 rounded-lg p-5">
         <h3 class="text-sm font-semibold mb-3">Create New Profile</h3>
         <form method="POST" action="{{ route('temperature-control.store-profile') }}" class="flex items-end gap-3">
            @csrf
            <div class="flex-1">
               <label class="block text-[10px] text-neutral-400 mb-1">Profile Name</label>
               <input type="text" name="name" required placeholder="e.g. Summer Mode"
                  class="w-full border border-neutral-300 rounded px-2 py-1.5 text-xs focus:border-black focus:ring-0 focus:outline-none">
            </div>
            <button type="submit"
               class="px-4 py-1.5 bg-black text-white text-xs rounded hover:bg-neutral-800 transition-colors whitespace-nowrap">
               Create Profile
            </button>
         </form>
      </div>

      {{-- Profiles List --}}
      @forelse($profiles as $profile)
      <div class="border border-neutral-200 rounded-lg p-5">
         <div class="flex items-center justify-between mb-4">
            <div class="flex items-center space-x-2">
               <h3 class="text-sm font-semibold">{{ $profile->name }}</h3>
               @if($profile->is_active)
                  <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium border border-black text-black">
                     Active
                  </span>
               @endif
            </div>
            <div class="flex items-center space-x-3">
               @if(!$profile->is_active)
                  <form method="POST" action="{{ route('temperature-control.activate', $profile) }}">
                     @csrf
                     <button type="submit"
                        class="px-3 py-1 bg-black text-white text-xs rounded hover:bg-neutral-800 transition">
                        Activate
                     </button>
                  </form>
               @endif
               <form method="POST" action="{{ route('temperature-control.destroy-profile', $profile) }}"
                  onsubmit="return confirm('Delete this profile and all its rules?')">
                  @csrf
                  @method('DELETE')
                  <button type="submit"
                     class="text-[10px] text-neutral-400 hover:text-black underline underline-offset-2">
                     Delete
                  </button>
               </form>
            </div>
         </div>

         {{-- Rules Table --}}
         @if($profile->rules->count() > 0)
         <div class="overflow-x-auto">
            <table class="min-w-full">
               <thead>
                  <tr class="border-b border-neutral-200">
                     <th class="px-3 py-2 text-left text-[10px] font-medium text-neutral-400 uppercase tracking-wider">Temperature</th>
                     <th class="px-3 py-2 text-left text-[10px] font-medium text-neutral-400 uppercase tracking-wider">Fan Speed</th>
                     <th class="px-3 py-2 text-right text-[10px] font-medium text-neutral-400 uppercase tracking-wider">Action</th>
                  </tr>
               </thead>
               <tbody class="divide-y divide-neutral-100">
                  @foreach($profile->rules->sortBy('temperature') as $rule)
                  <tr>
                     <td class="px-3 py-2 text-xs">>= {{ $rule->temperature }} C</td>
                     <td class="px-3 py-2 text-xs">{{ $rule->fan_speed_percent }}%</td>
                     <td class="px-3 py-2 text-right">
                        <form method="POST" action="{{ route('temperature-control.destroy-rule', [$profile, $rule]) }}" class="inline">
                           @csrf
                           @method('DELETE')
                           <button type="submit" class="text-[10px] text-neutral-400 hover:text-black underline">Remove</button>
                        </form>
                     </td>
                  </tr>
                  @endforeach
               </tbody>
            </table>
         </div>
         @else
            <p class="text-xs text-neutral-400 text-center py-3">No rules yet. Add rules below.</p>
         @endif

         {{-- Add Rule Form --}}
         <form method="POST" action="{{ route('temperature-control.store-rule', $profile) }}"
            class="flex flex-col sm:flex-row items-end gap-3 mt-3 pt-3 border-t border-neutral-100">
            @csrf
            <div>
               <label class="block text-[10px] text-neutral-400 mb-1">Temperature (C)</label>
               <input type="number" name="temperature" step="0.1" required placeholder="e.g. 28"
                  class="w-24 border border-neutral-300 rounded px-2 py-1.5 text-xs focus:border-black focus:ring-0 focus:outline-none">
            </div>
            <div>
               <label class="block text-[10px] text-neutral-400 mb-1">Fan Speed (%)</label>
               <input type="number" name="fan_speed_percent" min="0" max="100" required placeholder="e.g. 50"
                  class="w-24 border border-neutral-300 rounded px-2 py-1.5 text-xs focus:border-black focus:ring-0 focus:outline-none">
            </div>
            <button type="submit"
               class="px-3 py-1.5 bg-black text-white text-xs rounded hover:bg-neutral-800 transition-colors whitespace-nowrap">
               Add Rule
            </button>
         </form>
      </div>
      @empty
         {{-- No profiles message --}}
      @endforelse
   </div>

   {{-- Right Column --}}
   <div class="space-y-6">

      {{-- Current Fan Status --}}
      <div class="border border-neutral-200 rounded-lg p-5">
         <h3 class="text-sm font-semibold mb-3">Current Fan Status</h3>
         <div class="space-y-3">
            <div class="flex items-center justify-between">
               <span class="text-xs text-neutral-500">Power</span>
               <span id="tc-fan-status" class="text-xs font-medium text-neutral-400">Loading...</span>
            </div>
            <div class="flex items-center justify-between">
               <span class="text-xs text-neutral-500">Speed</span>
               <span id="tc-fan-speed" class="text-xs font-medium text-neutral-400">--</span>
            </div>
            <div class="flex items-center justify-between">
               <span class="text-xs text-neutral-500">Mode</span>
               <span id="tc-fan-mode" class="text-xs font-medium {{ $tempControlActive ? 'text-black' : 'text-neutral-400' }}">
                  {{ $tempControlActive ? 'Temperature Control' : 'Manual' }}
               </span>
            </div>
         </div>
      </div>

      {{-- How It Works --}}
      <div class="border border-neutral-200 rounded-lg p-5">
         <h3 class="text-sm font-semibold mb-3">How It Works</h3>
         <div class="text-[11px] text-neutral-500 space-y-2">
            <p><strong>1.</strong> Create a profile and add temperature rules.</p>
            <p><strong>2.</strong> Each rule defines: at what temperature the fan should run at what speed.</p>
            <p><strong>3.</strong> Click <strong>Activate</strong> to enable automatic control.</p>
            <p><strong>4.</strong> When the ESP32 sends a temperature reading, the server evaluates your rules:</p>
            <div class="bg-neutral-50 border border-neutral-200 rounded p-2 mt-1 space-y-1 font-mono text-[10px]">
               <p>temp >= 35C &rarr; fan 100%</p>
               <p>temp >= 33C &rarr; fan 75%</p>
               <p>temp >= 28C &rarr; fan 50%</p>
               <p>temp &lt; 28C &rarr; fan OFF</p>
            </div>
            <p class="mt-2">The fan speed updates automatically on the next sensor reading (every ~10 seconds).</p>
            <p>You can <strong>Deactivate</strong> at any time to return to manual control.</p>
         </div>
      </div>
   </div>
</div>
@endsection

@push('scripts')
<script>
let sensorChart = null;

// ─── Sensor Chart (same as dashboard) ───
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
         params: { sensor_type: sensorType, hours: hours }
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
            plugins: { legend: { display: false } },
            scales: {
               x: {
                  grid: { display: false },
                  ticks: { maxTicksLimit: 12, font: { size: 10 }, color: '#a3a3a3' },
                  border: { color: '#e5e5e5' },
               },
               y: {
                  grid: { color: '#f5f5f5' },
                  ticks: { font: { size: 10 }, color: '#a3a3a3' },
                  border: { color: '#e5e5e5' },
               }
            }
         }
      });
   } catch (e) {
      document.getElementById('chart-empty').classList.remove('hidden');
   }
}

// ─── Fan Status Polling ───
async function fetchFanStatus() {
   try {
      const res = await axios.get('/api/fan/status');
      const isOn = res.data.status === 'on';
      const speed = res.data.speed;
      const percent = Math.round(speed / 255 * 100);

      document.getElementById('tc-fan-status').textContent = isOn ? 'ON' : 'OFF';
      document.getElementById('tc-fan-status').className = 'text-xs font-medium ' + (isOn ? 'text-black' : 'text-neutral-400');
      document.getElementById('tc-fan-speed').textContent = speed + ' / 255 (' + percent + '%)';
   } catch (e) {
      document.getElementById('tc-fan-status').textContent = 'Error';
   }
}

// ─── Init ───
loadChartData();
setInterval(loadChartData, 30000);
fetchFanStatus();
setInterval(fetchFanStatus, 3000);
</script>
@endpush
