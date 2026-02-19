<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>IoT Dashboard</title>
   @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100 min-h-screen font-sans">
   <nav class="bg-white shadow">
      <div class="max-w-4xl mx-auto px-6 py-4">
         <h1 class="text-xl font-semibold text-gray-800">Fan Dashboard</h1>
      </div>
   </nav>

   <main class="max-w-4xl mx-auto px-6 py-10">
      <div class="bg-white rounded-2xl shadow p-8 max-w-sm mx-auto text-center">
         {{-- Fan icon --}}
         <div class="mb-6">
            <svg id="fan-icon" class="w-24 h-24 mx-auto text-gray-400 transition-colors duration-300"
               xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"
               stroke-width="1.5">
               <path stroke-linecap="round" stroke-linejoin="round" d="M12 12c0-2.5-2-4.5-4.5-4.5S3 7 3 9s2.5 3 4.5 3H12z
                           M12 12c2.5 0 4.5-2 4.5-4.5S17 3 15 3s-3 2.5-3 4.5V12z
                           M12 12c0 2.5 2 4.5 4.5 4.5S21 17 21 15s-2.5-3-4.5-3H12z
                           M12 12c-2.5 0-4.5 2-4.5 4.5S7 21 9 21s3-2.5 3-4.5V12z" />
               <circle cx="12" cy="12" r="1.5" fill="currentColor" />
            </svg>
         </div>

         {{-- Status --}}
         <div class="mb-6">
            <span class="text-sm font-medium text-gray-500 uppercase tracking-wide">Fan Status</span>
            <p id="fan-status" class="mt-1 text-3xl font-bold text-gray-400">--</p>
         </div>

         {{-- Toggle button --}}
         <button id="fan-toggle" onclick="toggleFan()" disabled
            class="w-full py-3 px-6 rounded-xl font-semibold text-white bg-gray-400 transition-colors duration-300 cursor-not-allowed">
            Loading...
         </button>

         {{-- Speed slider --}}
         <div id="speed-section" class="mt-6 pt-6 border-t border-gray-200 hidden">
            <label for="speed-slider" class="text-sm font-medium text-gray-500 uppercase tracking-wide">Fan
               Speed</label>
            <div class="mt-3 flex items-center gap-3">
               <span class="text-xs text-gray-400">0</span>
               <input id="speed-slider" type="range" min="0" max="255" value="255"
                  class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-green-500"
                  oninput="onSpeedChange(this.value)">
               <span class="text-xs text-gray-400">100</span>
            </div>
            <p id="speed-value" class="mt-1 text-lg font-semibold text-gray-700">255</p>
         </div>

         <p id="last-updated" class="mt-4 text-xs text-gray-400"></p>
      </div>
   </main>

   <script>
   let currentState = null;
   let currentSpeed = 255;
   let speedTimeout = null;

   async function fetchStatus() {
      try {
         const res = await axios.get('/api/fan/status');
         const status = res.data.status;
         const speed = res.data.speed;
         updateUI(status, speed);
      } catch (e) {
         document.getElementById('fan-status').textContent = 'Error';
      }
   }

   function updateUI(status, speed) {
      currentState = status;
      if (speed !== undefined) currentSpeed = speed;
      const isOn = status === 'on';

      const statusEl = document.getElementById('fan-status');
      const toggleBtn = document.getElementById('fan-toggle');
      const fanIcon = document.getElementById('fan-icon');
      const lastUpdated = document.getElementById('last-updated');
      const speedSection = document.getElementById('speed-section');
      const speedSlider = document.getElementById('speed-slider');
      const speedValue = document.getElementById('speed-value');

      statusEl.textContent = isOn ? 'ON' : 'OFF';
      statusEl.className = 'mt-1 text-3xl font-bold ' + (isOn ? 'text-green-500' : 'text-gray-400');

      toggleBtn.disabled = false;
      toggleBtn.className =
         'w-full py-3 px-6 rounded-xl font-semibold text-white transition-colors duration-300 cursor-pointer ' +
         (isOn ? 'bg-red-500 hover:bg-red-600' : 'bg-green-500 hover:bg-green-600');
      toggleBtn.textContent = isOn ? 'Turn Off' : 'Turn On';

      fanIcon.className = 'w-24 h-24 mx-auto transition-colors duration-300 ' +
         (isOn ? 'text-green-500 animate-spin' : 'text-gray-400');

      // Show speed slider only when fan is on
      if (isOn) {
         speedSection.classList.remove('hidden');
      } else {
         speedSection.classList.add('hidden');
      }

      speedSlider.value = currentSpeed;
      speedValue.textContent = currentSpeed;

      const now = new Date();
      lastUpdated.textContent = 'Last updated: ' + now.toLocaleTimeString();
   }

   function onSpeedChange(value) {
      document.getElementById('speed-value').textContent = value;
      // Debounce: wait 300ms after user stops sliding before sending
      clearTimeout(speedTimeout);
      speedTimeout = setTimeout(() => sendSpeed(value), 300);
   }

   async function sendSpeed(value) {
      try {
         await axios.get('/api/fan/speed/' + value);
      } catch (e) {
         console.error('Failed to set speed', e);
      }
   }

   async function toggleFan() {
      const btn = document.getElementById('fan-toggle');
      btn.disabled = true;
      btn.textContent = 'Sending...';

      try {
         const endpoint = currentState === 'on' ? '/api/fan/off' : '/api/fan/on';
         await axios.get(endpoint);
         await fetchStatus();
      } catch (e) {
         btn.textContent = 'Error — try again';
         btn.disabled = false;
      }
   }

   // Initial fetch and auto-refresh every 3 seconds
   fetchStatus();
   setInterval(fetchStatus, 3000);
   </script>
</body>

</html>