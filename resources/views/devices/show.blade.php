@extends('layouts.app')
@section('title', $device->name)

@section('content')
<div class="mb-5">
   <a href="{{ route('devices.index') }}" class="text-xs text-neutral-400 hover:text-black">&larr; Back to Devices</a>
   <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mt-1.5 gap-3">
      <div class="flex items-center space-x-2.5">
         <div class="w-2 h-2 rounded-full {{ $device->status === 'online' ? 'bg-black' : 'bg-neutral-300' }}"></div>
         <h2 class="text-lg font-semibold">{{ $device->name }}</h2>
         <span
            class="text-[10px] font-medium px-2 py-0.5 rounded border {{ $device->status === 'online' ? 'border-black text-black' : 'border-neutral-300 text-neutral-400' }}">
            {{ ucfirst($device->status) }}
         </span>
      </div>
      <div class="flex space-x-2">
         <a href="{{ route('devices.edit', $device) }}"
            class="px-3 py-1.5 text-xs font-medium border border-neutral-300 rounded hover:bg-neutral-50 transition">Edit</a>
         <form method="POST" action="{{ route('devices.destroy', $device) }}"
            onsubmit="return confirm('Delete this device?')">
            @csrf @method('DELETE')
            <button type="submit"
               class="px-3 py-1.5 text-xs font-medium bg-black text-white rounded hover:bg-neutral-800 transition">Delete</button>
         </form>
      </div>
   </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
   <div class="lg:col-span-2 space-y-5">
      {{-- Info --}}
      <div class="border border-neutral-200 rounded-lg p-5">
         <h3 class="text-sm font-semibold mb-3">Device Information</h3>
         <dl class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
               <dt class="text-[10px] text-neutral-400 uppercase tracking-wide">Type</dt>
               <dd class="text-xs font-medium mt-0.5">{{ strtoupper($device->device_type) }}</dd>
            </div>
            <div>
               <dt class="text-[10px] text-neutral-400 uppercase tracking-wide">Identifier</dt>
               <dd class="text-xs font-mono mt-0.5">{{ $device->device_identifier }}</dd>
            </div>
            <div>
               <dt class="text-[10px] text-neutral-400 uppercase tracking-wide">Location</dt>
               <dd class="text-xs font-medium mt-0.5">{{ $device->location ?? '—' }}</dd>
            </div>
            <div>
               <dt class="text-[10px] text-neutral-400 uppercase tracking-wide">Last Seen</dt>
               <dd class="text-xs font-medium mt-0.5">
                  {{ $device->last_seen_at ? $device->last_seen_at->diffForHumans() : 'Never' }}
               </dd>
            </div>
            @if($device->description)
            <div class="col-span-2">
               <dt class="text-[10px] text-neutral-400 uppercase tracking-wide">Description</dt>
               <dd class="text-xs mt-0.5">{{ $device->description }}</dd>
            </div>
            @endif
         </dl>
      </div>

      {{-- Chart --}}
      <div class="border border-neutral-200 rounded-lg p-5">
         <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-3">
            <h3 class="text-sm font-semibold">Sensor History</h3>
            <div class="flex flex-wrap gap-2">
               <select id="chart-sensor" class="text-xs border border-neutral-300 rounded px-2 py-1 bg-white"
                  onchange="loadDeviceChart()">
                  <option value="temperature">Temperature</option>
                  <option value="humidity">Humidity</option>
                  <option value="light">Light</option>
                  <option value="pressure">Pressure</option>
                  <option value="gas">Gas</option>
               </select>
               <select id="chart-hours" class="text-xs border border-neutral-300 rounded px-2 py-1 bg-white"
                  onchange="loadDeviceChart()">
                  <option value="1">1h</option>
                  <option value="6">6h</option>
                  <option value="24" selected>24h</option>
                  <option value="168">7d</option>
               </select>
            </div>
         </div>
         <div class="h-56">
            <canvas id="deviceChart"></canvas>
         </div>
         <p id="chart-empty" class="text-center text-neutral-400 text-xs mt-3 hidden">No data available.</p>
      </div>

      {{-- Send Command --}}
      <div class="border border-neutral-200 rounded-lg p-5">
         <h3 class="text-sm font-semibold mb-3">Send Command</h3>
         <form method="POST" action="{{ route('devices.command', $device) }}"
            class="flex flex-col sm:flex-row sm:items-end gap-3">
            @csrf
            <div class="flex-1">
               <label for="command"
                  class="block text-[10px] text-neutral-400 uppercase tracking-wide mb-1">Command</label>
               <select name="command" id="command"
                  class="w-full rounded border border-neutral-300 px-3 py-2 text-sm bg-white focus:border-black focus:ring-0 focus:outline-none">
                  <option value="on">Turn On</option>
                  <option value="off">Turn Off</option>
                  <option value="restart">Restart</option>
                  <option value="set_speed">Set Speed</option>
                  <option value="configure">Configure</option>
               </select>
            </div>
            <button type="submit"
               class="px-4 py-2 bg-black text-white text-xs font-medium rounded hover:bg-neutral-800 transition">Send</button>
         </form>
      </div>
   </div>

   <div class="space-y-5">
      {{-- Current Readings --}}
      <div class="border border-neutral-200 rounded-lg p-5">
         <h3 class="text-sm font-semibold mb-3">Current Readings</h3>
         @forelse($latestData as $type => $reading)
         <div class="flex items-center justify-between py-2 {{ !$loop->last ? 'border-b border-neutral-100' : '' }}">
            <span class="text-xs text-neutral-500">{{ ucfirst($type) }}</span>
            <span class="text-sm font-semibold">{{ $reading->value }} <span
                  class="text-[10px] text-neutral-400">{{ $reading->unit }}</span></span>
         </div>
         @empty
         <p class="text-xs text-neutral-400 text-center py-3">No readings yet</p>
         @endforelse
      </div>

      {{-- Recent Commands --}}
      <div class="border border-neutral-200 rounded-lg p-5">
         <h3 class="text-sm font-semibold mb-3">Recent Commands</h3>
         @forelse($device->commands()->latest()->limit(5)->get() as $cmd)
         <div class="flex items-center justify-between py-2 {{ !$loop->last ? 'border-b border-neutral-50' : '' }}">
            <div>
               <p class="text-xs font-medium">{{ $cmd->command }}</p>
               <p class="text-[10px] text-neutral-400">{{ $cmd->created_at->diffForHumans() }}</p>
            </div>
            <span
               class="text-[10px] font-medium px-1.5 py-0.5 rounded border {{ $cmd->status === 'executed' ? 'border-black text-black' : 'border-neutral-300 text-neutral-400' }}">
               {{ ucfirst($cmd->status) }}
            </span>
         </div>
         @empty
         <p class="text-xs text-neutral-400 text-center py-3">No commands sent</p>
         @endforelse
      </div>
   </div>
</div>
@endsection

@push('scripts')
<script>
let deviceChart = null;

async function loadDeviceChart() {
   const sensorType = document.getElementById('chart-sensor').value;
   const hours = document.getElementById('chart-hours').value;

   try {
      const res = await axios.get(`/api/devices/{{ $device->id }}/sensor-data/chart`, {
         params: {
            sensor_type: sensorType,
            hours: hours
         }
      });

      if (!res.data.labels.length) {
         document.getElementById('chart-empty').classList.remove('hidden');
         if (deviceChart) deviceChart.destroy();
         return;
      }

      document.getElementById('chart-empty').classList.add('hidden');
      if (deviceChart) deviceChart.destroy();

      const ctx = document.getElementById('deviceChart').getContext('2d');
      deviceChart = new Chart(ctx, {
         type: 'line',
         data: {
            labels: res.data.labels,
            datasets: [{
               label: sensorType.charAt(0).toUpperCase() + sensorType.slice(1),
               data: res.data.values,
               borderColor: '#000000',
               backgroundColor: 'rgba(0, 0, 0, 0.05)',
               fill: true,
               tension: 0.4,
               pointRadius: 1.5,
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

loadDeviceChart();
</script>
@endpush