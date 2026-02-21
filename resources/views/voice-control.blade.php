@extends('layouts.app')
@section('title', 'Voice Control — IoT Monitor')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
   <h1 class="text-xl font-semibold text-black">Voice Control</h1>
   <div class="flex items-center space-x-2" id="sinric-status">
      <span id="sinric-status-dot" class="w-2 h-2 rounded-full bg-neutral-300"></span>
      <span id="sinric-status-text" class="text-[10px] text-neutral-400">Checking...</span>
   </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
   {{-- Left Column --}}
   <div class="lg:col-span-2 space-y-6">

      {{-- Google Assistant Commands --}}
      <div class="border border-neutral-200 rounded-lg p-5">
         <div class="flex items-center space-x-2 mb-4">
            <svg class="w-4 h-4 text-black" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
               <path stroke-linecap="round" stroke-linejoin="round"
                  d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
            </svg>
            <h3 class="text-sm font-semibold">Voice Commands</h3>
         </div>

         {{-- ★ Speak Command Section --}}
         <div class="mb-5 border border-neutral-200 rounded-lg p-4 bg-neutral-50">
            <div class="flex flex-col sm:flex-row items-center gap-4">
               {{-- Mic Button --}}
               <button id="mic-btn" onclick="toggleSpeechRecognition()"
                  class="relative flex items-center justify-center w-16 h-16 rounded-full border-2 border-neutral-300 bg-white hover:border-black transition-all duration-200 shrink-0 group"
                  aria-label="Click to speak a command">
                  <svg id="mic-icon" class="w-7 h-7 text-neutral-400 group-hover:text-black transition" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                     <path stroke-linecap="round" stroke-linejoin="round"
                        d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                  </svg>
                  {{-- Pulse ring when active --}}
                  <span id="mic-pulse"
                     class="absolute inset-0 rounded-full border-2 border-black animate-ping opacity-0 pointer-events-none"></span>
               </button>

               <div class="flex-1 text-center sm:text-left">
                  <p id="mic-status" class="text-sm font-medium text-neutral-500">Click the mic or say "Hey Fan"</p>
                  <p id="mic-transcript" class="text-xs text-neutral-400 mt-1 min-h-[1.25rem]"></p>
                  <div id="mic-unsupported" class="hidden text-xs text-neutral-500 mt-1">
                     Speech recognition is not supported in this browser. Please use Chrome or Edge.
                  </div>
               </div>
            </div>

            {{-- Hands-Free Mode --}}
            <div class="mt-4 pt-3 border-t border-neutral-200">
               <div class="flex items-center justify-between">
                  <div>
                     <p class="text-xs font-medium text-black">Hands-Free Mode</p>
                     <!-- <p class="text-[10px] text-neutral-400 mt-0.5">Listens continuously — no clicking needed. Say "Hey
                        Fan" followed by your command.</p> -->
                  </div>
                  <label class="relative inline-flex items-center cursor-pointer">
                     <input type="checkbox" id="handsfree-toggle" onchange="toggleHandsFreeMode()" class="sr-only peer">
                     <div
                        class="w-9 h-5 bg-neutral-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-black">
                     </div>
                  </label>
               </div>
            </div>

            {{-- Audio Feedback Toggle --}}
            <div class="mt-3 pt-3 border-t border-neutral-200">
               <div class="flex items-center justify-between">
                  <div>
                     <p class="text-xs font-medium text-black">Audio Feedback</p>
                     <p class="text-[10px] text-neutral-400 mt-0.5">Speaks confirmation aloud after each command (for
                        visually impaired users).</p>
                  </div>
                  <label class="relative inline-flex items-center cursor-pointer">
                     <input type="checkbox" id="audio-feedback-toggle" checked class="sr-only peer">
                     <div
                        class="w-9 h-5 bg-neutral-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-black">
                     </div>
                  </label>
               </div>
            </div>
         </div>

         <p class="text-xs text-neutral-400 mb-4">Click any command to simulate it via the Sinric Pro API, or use the
            microphone above to speak naturally.</p>

         <div class="space-y-1.5">
            <div onclick="executeVoiceCommand('turn_on', 'fan')"
               class="group flex items-center justify-between px-3 py-2.5 rounded border border-neutral-100 hover:border-black cursor-pointer transition">
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

            <div onclick="executeVoiceCommand('turn_off', 'fan')"
               class="group flex items-center justify-between px-3 py-2.5 rounded border border-neutral-100 hover:border-black cursor-pointer transition">
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

            <div onclick="executeVoiceCommand('set_speed', 'fan', 50)"
               class="group flex items-center justify-between px-3 py-2.5 rounded border border-neutral-100 hover:border-black cursor-pointer transition">
               <div class="flex items-center space-x-2.5">
                  <svg class="w-3.5 h-3.5 text-neutral-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                     stroke-width="2">
                     <path stroke-linecap="round" stroke-linejoin="round"
                        d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                  </svg>
                  <span class="text-xs">"Hey Gout, set fan speed to 50%"</span>
               </div>
               <span
                  class="text-[10px] text-neutral-400 hidden sm:inline sm:opacity-0 sm:group-hover:opacity-100 transition">Click
                  to
                  simulate</span>
            </div>

            <div onclick="executeVoiceCommand('get_temperature', 'sensor')"
               class="group flex items-center justify-between px-3 py-2.5 rounded border border-neutral-100 hover:border-black cursor-pointer transition">
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

            <div onclick="executeVoiceCommand('get_humidity', 'sensor')"
               class="group flex items-center justify-between px-3 py-2.5 rounded border border-neutral-100 hover:border-black cursor-pointer transition">
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

            <div onclick="executeVoiceCommand('toggle_all', 'all')"
               class="group flex items-center justify-between px-3 py-2.5 rounded border border-neutral-100 hover:border-black cursor-pointer transition">
               <div class="flex items-center space-x-2.5">
                  <svg class="w-3.5 h-3.5 text-neutral-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                     stroke-width="2">
                     <path stroke-linecap="round" stroke-linejoin="round"
                        d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                  </svg>
                  <span class="text-xs">"Hey Gout, toggle all devices"</span>
               </div>
               <span
                  class="text-[10px] text-neutral-400 hidden sm:inline sm:opacity-0 sm:group-hover:opacity-100 transition">Click
                  to
                  simulate</span>
            </div>
         </div>
      </div>

      {{-- Custom Command --}}
      <div class="border border-neutral-200 rounded-lg p-5">
         <h3 class="text-sm font-semibold mb-3">Custom Command</h3>
         <div class="flex flex-col sm:flex-row sm:items-end gap-3">
            <div class="flex-1 space-y-3">
               <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                  <div>
                     <label class="block text-[10px] text-neutral-400 mb-1">Action</label>
                     <select id="custom-action"
                        class="w-full border border-neutral-300 rounded px-2 py-1.5 text-xs focus:border-black focus:ring-0 focus:outline-none bg-white">
                        <option value="turn_on">Turn On</option>
                        <option value="turn_off">Turn Off</option>
                        <option value="set_speed">Set Speed</option>
                        <option value="get_temperature">Get Temperature</option>
                        <!-- <option value="get_humidity">Get Humidity</option>
                        <option value="toggle_all">Toggle All</option> -->
                     </select>
                  </div>
                  <div>
                     <label class="block text-[10px] text-neutral-400 mb-1">Device</label>
                     <select id="custom-device"
                        class="w-full border border-neutral-300 rounded px-2 py-1.5 text-xs focus:border-black focus:ring-0 focus:outline-none bg-white">
                        <option value="fan">Fan</option>
                        <!-- <option value="sensor">Sensor</option> -->
                        <option value="all">All Devices</option>
                     </select>
                  </div>
                  <div>
                     <label class="block text-[10px] text-neutral-400 mb-1">Value (optional)</label>
                     <input id="custom-value" type="number" min="0" max="100" placeholder="e.g. 75"
                        class="w-full border border-neutral-300 rounded px-2 py-1.5 text-xs focus:border-black focus:ring-0 focus:outline-none">
                  </div>
               </div>
            </div>
            <button onclick="sendCustomCommand()"
               class="px-4 py-1.5 bg-black text-white text-xs rounded hover:bg-neutral-800 transition-colors whitespace-nowrap">
               Execute
            </button>
         </div>
      </div>

      {{-- Command Log --}}
      <div class="border border-neutral-200 rounded-lg p-5">
         <div class="flex items-center justify-between mb-3">
            <h3 class="text-sm font-semibold">Command Log</h3>
            <button onclick="clearCommandLog()"
               class="text-[10px] underline text-neutral-400 hover:text-black">Clear</button>
         </div>
         <div id="voice-command-log"
            class="bg-neutral-50 rounded p-4 max-h-60 overflow-y-auto font-mono text-[11px] text-neutral-600 space-y-1">
            <p class="text-neutral-400 italic">No commands executed yet.</p>
         </div>
      </div>
   </div>

   {{-- Right Column --}}
   <div class="space-y-6">

      {{-- Quick Actions --}}
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
            <!-- <button onclick="executeVoiceCommand('get_humidity', 'sensor')"
               class="w-full text-left flex items-center justify-between px-3 py-2 border border-neutral-200 rounded hover:border-black transition text-xs">
               <span>Get Humidity</span>
               <svg class="w-3 h-3 text-neutral-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                  stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
               </svg>
            </button> -->
            <button onclick="executeVoiceCommand('toggle_all', 'all')"
               class="w-full text-left flex items-center justify-between px-3 py-2 border border-neutral-200 rounded hover:border-black transition text-xs">
               <span>Toggle All Devices</span>
               <svg class="w-3 h-3 text-neutral-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                  stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
               </svg>
            </button>
         </div>
      </div>

      {{-- Current Fan Status --}}
      <div class="border border-neutral-200 rounded-lg p-5">
         <h3 class="text-sm font-semibold mb-3">Current Fan Status</h3>
         <div id="fan-status-box" class="space-y-3">
            <div class="flex items-center justify-between">
               <span class="text-xs text-neutral-500">Power</span>
               <span id="vc-fan-status" class="text-xs font-medium text-neutral-400">Loading...</span>
            </div>
            <div class="flex items-center justify-between">
               <span class="text-xs text-neutral-500">Speed</span>
               <span id="vc-fan-speed" class="text-xs font-medium text-neutral-400">—</span>
            </div>
         </div>
      </div>

      {{-- Sinric Pro Setup --}}
      <!-- <div class="border border-neutral-200 rounded-lg p-5">
         <h3 class="text-sm font-semibold mb-3">Sinric Pro Setup</h3>
         <div class="text-[11px] text-neutral-500 space-y-2">
            <p><strong>1.</strong> Create account at <span class="font-mono">sinric.pro</span></p>
            <p><strong>2.</strong> Add ESP32 device (type: Fan / Temperature Sensor)</p>
            <p><strong>3.</strong> Add to <span class="font-mono">.env</span>:</p>
            <pre class="bg-neutral-50 border border-neutral-200 rounded p-2 overflow-x-auto text-[10px]">SINRIC_APP_KEY=your-app-key
SINRIC_APP_SECRET=your-app-secret
SINRIC_DEVICE_ID=your-device-id</pre>
            <p><strong>4.</strong> Link Sinric Pro in Google Home → <strong>Works with Google</strong></p>
            <p><strong>5.</strong> Upload ESP32 code with Sinric Pro library</p>
            <div class="border-t border-neutral-100 pt-2 mt-2">
               <p><strong>Callback API:</strong></p>
               <p class="font-mono text-[10px]">POST /api/sinric/callback</p>
            </div>
         </div>
      </div>
   </div>
</div> -->
      @endsection

      @push('scripts')
      <script>
      // ─── Speech Recognition (Web Speech API) ───
      let recognition = null;
      let isListening = false;
      let isHandsFree = false;
      let speechSynth = window.speechSynthesis;

      function initSpeechRecognition() {
         const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
         if (!SpeechRecognition) {
            document.getElementById('mic-unsupported').classList.remove('hidden');
            document.getElementById('mic-btn').disabled = true;
            document.getElementById('mic-btn').classList.add('opacity-40', 'cursor-not-allowed');
            return false;
         }

         recognition = new SpeechRecognition();
         recognition.continuous = false;
         recognition.interimResults = true;
         recognition.lang = 'en-US';
         recognition.maxAlternatives = 1;

         recognition.onstart = () => {
            isListening = true;
            updateMicUI(true);
            setMicStatus('Listening... speak now', 'text-black');
         };

         recognition.onresult = (event) => {
            let interim = '';
            let final = '';
            for (let i = event.resultIndex; i < event.results.length; i++) {
               const transcript = event.results[i][0].transcript;
               if (event.results[i].isFinal) {
                  final += transcript;
               } else {
                  interim += transcript;
               }
            }
            const display = final || interim;
            document.getElementById('mic-transcript').textContent = `"${display}"`;
            if (final) {
               processVoiceInput(final.trim().toLowerCase());
            }
         };

         recognition.onerror = (event) => {
            if (event.error === 'no-speech') {
               setMicStatus('No speech detected. Try again.', 'text-neutral-500');
            } else if (event.error === 'not-allowed') {
               setMicStatus('Microphone access denied. Please allow microphone.', 'text-neutral-500');
            } else if (event.error === 'aborted') {
               // silent
            } else {
               setMicStatus(`Error: ${event.error}`, 'text-neutral-500');
               logCommand(`Speech error: ${event.error}`, 'error');
            }
            isListening = false;
            updateMicUI(false);
         };

         recognition.onend = () => {
            isListening = false;
            updateMicUI(false);
            if (isHandsFree) {
               setTimeout(() => {
                  if (isHandsFree && !isListening) {
                     try {
                        recognition.start();
                     } catch (e) {}
                  }
               }, 500);
               setMicStatus('Hands-free active — say  + command', 'text-black');
            } else {
               setMicStatus('Click the mic or say', 'text-neutral-500');
            }
         };

         return true;
      }

      function toggleSpeechRecognition() {
         if (!recognition) return;
         if (isListening) {
            recognition.abort();
            isListening = false;
            updateMicUI(false);
            setMicStatus('Stopped. Click the mic to speak again.', 'text-neutral-500');
         } else {
            document.getElementById('mic-transcript').textContent = '';
            try {
               recognition.start();
            } catch (e) {
               setMicStatus('Error starting mic. Try again.', 'text-neutral-500');
            }
         }
      }

      function toggleHandsFreeMode() {
         isHandsFree = document.getElementById('handsfree-toggle').checked;
         if (isHandsFree) {
            if (!recognition && !initSpeechRecognition()) return;
            recognition.continuous = true;
            setMicStatus('Hands-free active — say "Hey Fan" + command', 'text-black');
            speakFeedback('Hands free mode activated. Say Hey Fan followed by your command.');
            logCommand('Hands-free mode enabled', 'info');
            if (!isListening) {
               try {
                  recognition.start();
               } catch (e) {}
            }
         } else {
            recognition.continuous = false;
            if (isListening) recognition.abort();
            setMicStatus('Click the mic or say "Hey Fan"', 'text-neutral-500');
            speakFeedback('Hands free mode deactivated.');
            logCommand('Hands-free mode disabled', 'info');
         }
      }

      function updateMicUI(active) {
         const btn = document.getElementById('mic-btn');
         const icon = document.getElementById('mic-icon');
         const pulse = document.getElementById('mic-pulse');
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

      function setMicStatus(text, colorClass) {
         const el = document.getElementById('mic-status');
         el.textContent = text;
         el.className = `text-sm font-medium ${colorClass}`;
      }

      function speakFeedback(message) {
         const toggle = document.getElementById('audio-feedback-toggle');
         if (!toggle || !toggle.checked) return;
         if (!speechSynth) return;
         speechSynth.cancel();
         const utterance = new SpeechSynthesisUtterance(message);
         utterance.rate = 1.1;
         utterance.pitch = 1;
         utterance.volume = 0.8;
         speechSynth.speak(utterance);
      }

      function processVoiceInput(text) {
         const wakeWords = ['hey fan', 'hey fans', 'a fan', 'hey van', 'hey fam'];
         let command = text;
         for (const w of wakeWords) {
            if (command.startsWith(w)) {
               command = command.substring(w.length).trim();
               break;
            }
         }
         if (!command || command.length < 2) {
            setMicStatus('I heard you! Now say your command.', 'text-black');
            speakFeedback('I am listening. What would you like me to do?');
            return;
         }

         logCommand(`Voice: "${text}"`, 'info');
         let matched = false;

         if (/turn\s*on.*fan|fan\s*on|start.*fan|enable.*fan|switch\s*on.*fan/.test(command)) {
            executeVoiceCommand('turn_on', 'fan');
            speakFeedback('Turning on the fan.');
            matched = true;
         } else if (/turn\s*off.*fan|fan\s*off|stop.*fan|disable.*fan|switch\s*off.*fan|shut.*off.*fan/.test(command)) {
            executeVoiceCommand('turn_off', 'fan');
            speakFeedback('Turning off the fan.');
            matched = true;
         } else if (/set.*speed|speed.*to|fan.*(\d+)\s*percent|set.*fan.*(\d+)/.test(command)) {
            const speedMatch = command.match(/(\d+)/);
            const speed = speedMatch ? parseInt(speedMatch[1]) : 50;
            executeVoiceCommand('set_speed', 'fan', Math.min(speed, 100));
            speakFeedback(`Setting fan speed to ${speed} percent.`);
            matched = true;
         } else if (/temperature|how hot|how warm|temp/.test(command)) {
            executeVoiceCommand('get_temperature', 'sensor');
            speakFeedback('Getting the current temperature.');
            matched = true;
         } else if (/humidity|how humid|moisture/.test(command)) {
            executeVoiceCommand('get_humidity', 'sensor');
            speakFeedback('Getting the current humidity.');
            matched = true;
         } else if (/toggle\s*all|all\s*devices|everything/.test(command)) {
            executeVoiceCommand('toggle_all', 'all');
            speakFeedback('Toggling all devices.');
            matched = true;
         } else if (/turn\s*on|switch\s*on|start|enable/.test(command)) {
            executeVoiceCommand('turn_on', 'fan');
            speakFeedback('Turning on the fan.');
            matched = true;
         } else if (/turn\s*off|switch\s*off|stop|disable|shut/.test(command)) {
            executeVoiceCommand('turn_off', 'fan');
            speakFeedback('Turning off the fan.');
            matched = true;
         }

         if (!matched) {
            setMicStatus(`Didn't understand: "${command}"`, 'text-neutral-500');
            speakFeedback(`Sorry, I didn't understand. Try saying turn on the fan, or set speed to 50 percent.`);
            logCommand(`Unrecognized: "${command}"`, 'error');
         } else {
            setMicStatus('Command executed!', 'text-black');
            setTimeout(() => {
               if (!isListening && !isHandsFree) {
                  setMicStatus('Click the mic or say "Hey Fan"', 'text-neutral-500');
               }
            }, 3000);
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
         try {
            const res = await axios.get('/api/sinric/status');
            if (res.data.connected) {
               dot.className = 'w-2 h-2 rounded-full bg-black';
               text.textContent = 'Connected — ' + (res.data.device_id || 'N/A');
            } else {
               dot.className = 'w-2 h-2 rounded-full bg-neutral-300';
               text.textContent = 'Not configured';
            }
         } catch (e) {
            dot.className = 'w-2 h-2 rounded-full bg-neutral-300';
            text.textContent = 'Not configured';
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
               action,
               device,
               value
            });
            if (res.data.success) {
               logCommand(res.data.message || `${description} — OK`, 'success');
               fetchFanStatus();
            } else {
               logCommand(res.data.message || `Failed: ${description}`, 'error');
            }
         } catch (e) {
            const msg = e.response?.data?.message || e.message || 'Request failed';
            logCommand(`Error: ${msg}`, 'error');
         }
      }

      function sendCustomCommand() {
         const action = document.getElementById('custom-action').value;
         const device = document.getElementById('custom-device').value;
         const value = document.getElementById('custom-value').value || null;
         executeVoiceCommand(action, device, value);
      }

      async function fetchFanStatus() {
         try {
            const res = await axios.get('/api/fan/status');
            document.getElementById('vc-fan-status').textContent = res.data.status === 'on' ? 'ON' : 'OFF';
            document.getElementById('vc-fan-status').className = 'text-xs font-medium ' + (res.data.status === 'on' ?
               'text-black' : 'text-neutral-400');
            document.getElementById('vc-fan-speed').textContent = res.data.speed + ' / 255';
         } catch (e) {
            document.getElementById('vc-fan-status').textContent = 'Error';
         }
      }

      // Init
      initSpeechRecognition();
      checkSinricStatus();
      fetchFanStatus();
      setInterval(fetchFanStatus, 5000);
      </script>
      @endpush