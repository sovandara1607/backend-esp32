<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ESP32 Fan Control</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 dark:bg-gray-950 min-h-full font-sans text-gray-900 dark:text-gray-100">

    {{-- ===== HEADER BAR ===== --}}
    <header class="bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-800 shadow-sm">
        <div class="max-w-3xl mx-auto px-4 py-4 flex items-center justify-between">
            <h1 class="text-xl font-bold tracking-tight">ESP32 Fan Control</h1>
            <div class="flex items-center gap-2">
                <span id="status-dot" class="w-3 h-3 rounded-full bg-gray-400"></span>
                <span id="status-text" class="text-sm font-medium text-gray-600 dark:text-gray-400">Fan OFF</span>
            </div>
        </div>
    </header>

    <main class="max-w-3xl mx-auto px-4 py-6">

        {{-- ===== TAB NAVIGATION ===== --}}
        <div class="flex border-b border-gray-200 dark:border-gray-700 mb-6">
            <button id="tab-manual" onclick="switchTab('manual')"
                class="tab-btn px-5 py-3 text-sm font-semibold border-b-2 border-blue-600 text-blue-600 dark:text-blue-400 dark:border-blue-400 transition-colors">
                Manual Control
            </button>
            <button id="tab-voice" onclick="switchTab('voice')"
                class="tab-btn px-5 py-3 text-sm font-semibold border-b-2 border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 transition-colors">
                Voice Control
            </button>
            <button id="tab-temperature" onclick="switchTab('temperature')"
                class="tab-btn px-5 py-3 text-sm font-semibold border-b-2 border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 transition-colors">
                Temperature Auto
            </button>
        </div>

        {{-- ===== TAB 1: MANUAL CONTROL ===== --}}
        <div id="panel-manual" class="tab-panel">
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6 shadow-sm">
                <h2 class="text-lg font-semibold mb-6">Manual Speed Control</h2>

                {{-- Speed display --}}
                <div class="text-center mb-8">
                    <div class="text-6xl font-bold text-blue-600 dark:text-blue-400" id="speed-display">0%</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400 mt-2" id="manual-status">Fan is OFF</div>
                </div>

                {{-- Range slider --}}
                <div class="mb-8">
                    <input type="range" id="speed-slider" min="0" max="100" value="0" step="1"
                        class="w-full h-3 bg-gray-200 dark:bg-gray-700 rounded-lg appearance-none cursor-pointer accent-blue-600">
                    <div class="flex justify-between text-xs text-gray-400 mt-1">
                        <span>0%</span>
                        <span>25%</span>
                        <span>50%</span>
                        <span>75%</span>
                        <span>100%</span>
                    </div>
                </div>

                {{-- Preset buttons --}}
                <div class="flex gap-3 flex-wrap">
                    <button onclick="setSpeed(0)"
                        class="px-5 py-2.5 rounded-lg bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 font-medium text-sm transition-colors">
                        OFF
                    </button>
                    <button onclick="setSpeed(25)"
                        class="px-5 py-2.5 rounded-lg bg-blue-100 dark:bg-blue-900/40 hover:bg-blue-200 dark:hover:bg-blue-900/60 text-blue-700 dark:text-blue-300 font-medium text-sm transition-colors">
                        25%
                    </button>
                    <button onclick="setSpeed(50)"
                        class="px-5 py-2.5 rounded-lg bg-blue-100 dark:bg-blue-900/40 hover:bg-blue-200 dark:hover:bg-blue-900/60 text-blue-700 dark:text-blue-300 font-medium text-sm transition-colors">
                        50%
                    </button>
                    <button onclick="setSpeed(75)"
                        class="px-5 py-2.5 rounded-lg bg-blue-100 dark:bg-blue-900/40 hover:bg-blue-200 dark:hover:bg-blue-900/60 text-blue-700 dark:text-blue-300 font-medium text-sm transition-colors">
                        75%
                    </button>
                    <button onclick="setSpeed(100)"
                        class="px-5 py-2.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-medium text-sm transition-colors">
                        100%
                    </button>
                </div>
            </div>
        </div>

        {{-- ===== TAB 2: VOICE CONTROL ===== --}}
        <div id="panel-voice" class="tab-panel hidden">
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6 shadow-sm">
                <h2 class="text-lg font-semibold mb-6">Voice Control</h2>

                {{-- Listen button --}}
                <div class="text-center mb-6">
                    <button id="voice-btn" onclick="toggleListening()"
                        class="w-20 h-20 rounded-full bg-green-600 hover:bg-green-700 text-white flex items-center justify-center mx-auto shadow-lg transition-all">
                        <svg id="mic-icon" xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 18.75a6 6 0 006-6v-1.5m-6 7.5a6 6 0 01-6-6v-1.5m6 7.5v3.75m-3.75 0h7.5M12 15.75a3 3 0 01-3-3V4.5a3 3 0 116 0v8.25a3 3 0 01-3 3z" />
                        </svg>
                    </button>
                    <div id="voice-status" class="mt-3 text-sm text-gray-500 dark:text-gray-400">Click microphone to start</div>
                    <div id="voice-pulse" class="hidden mt-2 text-red-500 text-xs font-medium animate-pulse">Listening...</div>
                </div>

                {{-- Recognized text --}}
                <div class="mb-4">
                    <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Recognized Text</label>
                    <div id="recognized-text" class="bg-gray-50 dark:bg-gray-800 rounded-lg px-4 py-3 text-sm min-h-[44px] border border-gray-200 dark:border-gray-700 italic text-gray-400">
                        Waiting for voice input...
                    </div>
                </div>

                {{-- Action taken --}}
                <div class="mb-6">
                    <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Action Taken</label>
                    <div id="action-taken" class="bg-gray-50 dark:bg-gray-800 rounded-lg px-4 py-3 text-sm min-h-[44px] border border-gray-200 dark:border-gray-700 text-gray-400">
                        --
                    </div>
                </div>

                {{-- Command history --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Command History</label>
                    <div id="command-history" class="bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 max-h-48 overflow-y-auto">
                        <div class="px-4 py-3 text-sm text-gray-400 italic">No commands yet</div>
                    </div>
                </div>

                {{-- Supported commands --}}
                <details class="mt-6">
                    <summary class="text-sm font-medium text-gray-500 dark:text-gray-400 cursor-pointer hover:text-gray-700 dark:hover:text-gray-300">
                        Supported Voice Commands
                    </summary>
                    <div class="mt-2 bg-gray-50 dark:bg-gray-800 rounded-lg p-4 text-sm text-gray-600 dark:text-gray-400 space-y-1">
                        <p><strong>"turn on the fan"</strong> &rarr; Fan ON at 100%</p>
                        <p><strong>"turn off the fan"</strong> &rarr; Fan OFF (0%)</p>
                        <p><strong>"speed 50 percent"</strong> &rarr; Set to 50%</p>
                        <p><strong>"turn on the fan with 75%"</strong> &rarr; Set to 75%</p>
                        <p><strong>"set speed to 25"</strong> &rarr; Set to 25%</p>
                    </div>
                </details>

                {{-- Browser support warning --}}
                <div id="voice-unsupported" class="hidden mt-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4 text-sm text-yellow-800 dark:text-yellow-200">
                    Your browser does not support the Web Speech API. Please use Google Chrome or Microsoft Edge.
                </div>
            </div>
        </div>

        {{-- ===== TAB 3: TEMPERATURE AUTO ===== --}}
        <div id="panel-temperature" class="tab-panel hidden">
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6 shadow-sm">
                <h2 class="text-lg font-semibold mb-6">Temperature Auto Control</h2>

                {{-- Temperature display --}}
                <div class="text-center mb-8">
                    <div class="text-5xl font-bold text-orange-500" id="current-temp">-- &deg;C</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400 mt-2" id="temp-updated">Waiting for data...</div>
                    <div class="text-lg font-semibold mt-3" id="esp-speed-display">
                        ESP32 Fan Speed: <span id="esp-speed" class="text-blue-600 dark:text-blue-400">--</span>
                    </div>
                </div>

                {{-- Threshold table --}}
                <div class="mb-6">
                    <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Temperature Thresholds</h3>
                    <div class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-gray-100 dark:bg-gray-800">
                                    <th class="px-4 py-2 text-left font-medium">Temperature</th>
                                    <th class="px-4 py-2 text-left font-medium">Fan Speed</th>
                                    <th class="px-4 py-2 text-center font-medium">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr id="threshold-0" class="border-t border-gray-200 dark:border-gray-700">
                                    <td class="px-4 py-3">Below 20&deg;C</td>
                                    <td class="px-4 py-3">0% (OFF)</td>
                                    <td class="px-4 py-3 text-center"><span class="threshold-dot w-3 h-3 rounded-full bg-gray-300 dark:bg-gray-600 inline-block"></span></td>
                                </tr>
                                <tr id="threshold-25" class="border-t border-gray-200 dark:border-gray-700">
                                    <td class="px-4 py-3">23&deg;C &ndash; 25&deg;C</td>
                                    <td class="px-4 py-3">25% (LOW)</td>
                                    <td class="px-4 py-3 text-center"><span class="threshold-dot w-3 h-3 rounded-full bg-gray-300 dark:bg-gray-600 inline-block"></span></td>
                                </tr>
                                <tr id="threshold-50" class="border-t border-gray-200 dark:border-gray-700">
                                    <td class="px-4 py-3">26&deg;C &ndash; 29&deg;C</td>
                                    <td class="px-4 py-3">50% (MED)</td>
                                    <td class="px-4 py-3 text-center"><span class="threshold-dot w-3 h-3 rounded-full bg-gray-300 dark:bg-gray-600 inline-block"></span></td>
                                </tr>
                                <tr id="threshold-100" class="border-t border-gray-200 dark:border-gray-700">
                                    <td class="px-4 py-3">30&deg;C and above</td>
                                    <td class="px-4 py-3">100% (HIGH)</td>
                                    <td class="px-4 py-3 text-center"><span class="threshold-dot w-3 h-3 rounded-full bg-gray-300 dark:bg-gray-600 inline-block"></span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Info note --}}
                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 text-sm text-blue-800 dark:text-blue-200">
                    <strong>Note:</strong> In Temperature Auto mode, the ESP32 reads the LM35 sensor and controls the fan speed locally based on the thresholds above. The dashboard displays the readings for monitoring.
                </div>
            </div>
        </div>

    </main>

    {{-- ===== JAVASCRIPT ===== --}}
    <script>
        // =====================================================
        // Global State
        // =====================================================
        let currentMode = 'manual';
        let currentSpeed = 0;
        let currentTemperature = null;
        let temperatureUpdatedAt = null;
        let debounceTimer = null;
        let recognition = null;
        let isListening = false;

        // =====================================================
        // Polling - sync UI with server every 2 seconds
        // =====================================================
        async function pollState() {
            try {
                const response = await axios.get('/api/fan/state');
                const data = response.data;
                currentSpeed = data.speed;
                currentMode = data.mode;
                currentTemperature = data.temperature;
                temperatureUpdatedAt = data.temperature_updated_at;
                updateUI(data);
            } catch (err) {
                console.error('Polling error:', err);
            }
        }

        // Initial poll immediately, then every 2 seconds
        pollState();
        setInterval(pollState, 2000);

        // =====================================================
        // UI Update
        // =====================================================
        function updateUI(data) {
            // Header status
            const statusDot = document.getElementById('status-dot');
            const statusText = document.getElementById('status-text');

            if (data.speed > 0) {
                statusDot.className = 'w-3 h-3 rounded-full bg-green-500 shadow-sm shadow-green-500/50';
                statusText.textContent = 'Fan ON at ' + data.speed + '%';
                statusText.className = 'text-sm font-medium text-green-600 dark:text-green-400';
            } else {
                statusDot.className = 'w-3 h-3 rounded-full bg-gray-400';
                statusText.textContent = 'Fan OFF';
                statusText.className = 'text-sm font-medium text-gray-600 dark:text-gray-400';
            }

            // Manual tab - update slider if user is not dragging
            const slider = document.getElementById('speed-slider');
            if (!slider.matches(':active')) {
                slider.value = data.speed;
                document.getElementById('speed-display').textContent = data.speed + '%';
                document.getElementById('manual-status').textContent =
                    data.speed > 0 ? 'Fan is ON' : 'Fan is OFF';
            }

            // Temperature tab
            updateTemperatureDisplay();

            // Sync tab highlight if mode changed externally
            syncActiveTab(data.mode);
        }

        // =====================================================
        // Tab Switching
        // =====================================================
        function switchTab(mode) {
            // Hide all panels
            document.querySelectorAll('.tab-panel').forEach(p => p.classList.add('hidden'));
            document.getElementById('panel-' + mode).classList.remove('hidden');

            // Update tab button styles
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('border-blue-600', 'text-blue-600', 'dark:text-blue-400', 'dark:border-blue-400');
                btn.classList.add('border-transparent', 'text-gray-500', 'dark:text-gray-400');
            });
            const activeBtn = document.getElementById('tab-' + mode);
            activeBtn.classList.remove('border-transparent', 'text-gray-500', 'dark:text-gray-400');
            activeBtn.classList.add('border-blue-600', 'text-blue-600', 'dark:text-blue-400', 'dark:border-blue-400');

            // Notify server of mode change
            axios.post('/api/fan/mode', { mode: mode }).catch(err => console.error(err));
            currentMode = mode;

            // Stop voice recognition when leaving voice tab
            if (mode !== 'voice' && isListening) {
                stopListening();
            }
        }

        function syncActiveTab(mode) {
            if (currentMode === mode) return;
            // Only sync visual state, don't re-POST
            document.querySelectorAll('.tab-panel').forEach(p => p.classList.add('hidden'));
            document.getElementById('panel-' + mode).classList.remove('hidden');

            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('border-blue-600', 'text-blue-600', 'dark:text-blue-400', 'dark:border-blue-400');
                btn.classList.add('border-transparent', 'text-gray-500', 'dark:text-gray-400');
            });
            const activeBtn = document.getElementById('tab-' + mode);
            activeBtn.classList.remove('border-transparent', 'text-gray-500', 'dark:text-gray-400');
            activeBtn.classList.add('border-blue-600', 'text-blue-600', 'dark:text-blue-400', 'dark:border-blue-400');
            currentMode = mode;
        }

        // =====================================================
        // Manual Control - Slider & Preset Buttons
        // =====================================================
        function setSpeed(speed) {
            const slider = document.getElementById('speed-slider');
            slider.value = speed;
            document.getElementById('speed-display').textContent = speed + '%';
            document.getElementById('manual-status').textContent =
                speed > 0 ? 'Fan is ON' : 'Fan is OFF';

            axios.post('/api/fan/speed', { speed: speed })
                .catch(err => console.error('setSpeed error:', err));
        }

        // Debounced slider input
        document.getElementById('speed-slider').addEventListener('input', function (e) {
            const val = parseInt(e.target.value);
            document.getElementById('speed-display').textContent = val + '%';
            document.getElementById('manual-status').textContent =
                val > 0 ? 'Fan is ON' : 'Fan is OFF';

            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(function () {
                axios.post('/api/fan/speed', { speed: val })
                    .catch(err => console.error('slider error:', err));
            }, 300);
        });

        // =====================================================
        // Voice Control - Web Speech API
        // =====================================================
        function initSpeechRecognition() {
            const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;

            if (!SpeechRecognition) {
                document.getElementById('voice-unsupported').classList.remove('hidden');
                document.getElementById('voice-btn').style.display = 'none';
                document.getElementById('voice-status').style.display = 'none';
                return;
            }

            recognition = new SpeechRecognition();
            recognition.continuous = true;
            recognition.interimResults = false;
            recognition.lang = 'en-US';

            recognition.onresult = function (event) {
                const last = event.results[event.results.length - 1];
                if (!last.isFinal) return;

                const transcript = last[0].transcript.trim().toLowerCase();
                document.getElementById('recognized-text').textContent = '"' + transcript + '"';
                document.getElementById('recognized-text').classList.remove('italic', 'text-gray-400');
                document.getElementById('recognized-text').classList.add('text-gray-900', 'dark:text-gray-100');
                processVoiceCommand(transcript);
            };

            recognition.onerror = function (event) {
                console.error('Speech error:', event.error);
                if (event.error === 'no-speech' || event.error === 'aborted') return;
                stopListening();
            };

            recognition.onend = function () {
                if (isListening) {
                    try { recognition.start(); } catch (e) { /* ignore */ }
                }
            };
        }

        function toggleListening() {
            if (isListening) {
                stopListening();
            } else {
                startListening();
            }
        }

        function startListening() {
            if (!recognition) return;
            isListening = true;
            try { recognition.start(); } catch (e) { /* already started */ }

            const btn = document.getElementById('voice-btn');
            btn.className = 'w-20 h-20 rounded-full bg-red-600 hover:bg-red-700 text-white flex items-center justify-center mx-auto shadow-lg transition-all animate-pulse';
            document.getElementById('voice-status').textContent = 'Listening...';
            document.getElementById('voice-pulse').classList.remove('hidden');
        }

        function stopListening() {
            isListening = false;
            if (recognition) {
                try { recognition.stop(); } catch (e) { /* ignore */ }
            }

            const btn = document.getElementById('voice-btn');
            btn.className = 'w-20 h-20 rounded-full bg-green-600 hover:bg-green-700 text-white flex items-center justify-center mx-auto shadow-lg transition-all';
            document.getElementById('voice-status').textContent = 'Click microphone to start';
            document.getElementById('voice-pulse').classList.add('hidden');
        }

        function processVoiceCommand(transcript) {
            let action = '';
            let speed = null;

            // Check "turn off" / "stop" / "shut off" FIRST (before "turn on" check)
            if (/turn\s*off|fan\s*off|shut\s*(off|down)|stop\s*(the\s*)?fan/i.test(transcript)) {
                speed = 0;
                action = 'Turned fan OFF (0%)';
            }
            // Check for percentage: "50 percent", "50%", "with 50 percent"
            else if (/(\d+)\s*(%|percent)/i.test(transcript)) {
                const match = transcript.match(/(\d+)\s*(%|percent)/i);
                speed = Math.min(100, Math.max(0, parseInt(match[1])));
                action = 'Set fan speed to ' + speed + '%';
            }
            // Check for "speed 50" or "set speed to 50"
            else if (/speed\s*(\d+)/i.test(transcript)) {
                const match = transcript.match(/speed\s*(\d+)/i);
                speed = Math.min(100, Math.max(0, parseInt(match[1])));
                action = 'Set fan speed to ' + speed + '%';
            }
            // Check "turn on" / "start"
            else if (/turn\s*on|fan\s*on|start\s*(the\s*)?fan/i.test(transcript)) {
                speed = 100;
                action = 'Turned fan ON (100%)';
            }
            else {
                action = 'Command not recognized';
            }

            // Update action display
            const actionEl = document.getElementById('action-taken');
            actionEl.textContent = action;
            if (speed !== null) {
                actionEl.className = 'bg-green-50 dark:bg-green-900/20 rounded-lg px-4 py-3 text-sm min-h-[44px] border border-green-200 dark:border-green-800 text-green-700 dark:text-green-300 font-medium';
            } else {
                actionEl.className = 'bg-yellow-50 dark:bg-yellow-900/20 rounded-lg px-4 py-3 text-sm min-h-[44px] border border-yellow-200 dark:border-yellow-800 text-yellow-700 dark:text-yellow-300';
            }

            if (speed !== null) {
                axios.post('/api/fan/speed', { speed: speed })
                    .catch(err => console.error(err));
                addToCommandHistory(transcript, action);
            }
        }

        function addToCommandHistory(transcript, action) {
            const history = document.getElementById('command-history');

            // Remove the "No commands yet" placeholder
            const placeholder = history.querySelector('.italic');
            if (placeholder) placeholder.remove();

            const entry = document.createElement('div');
            entry.className = 'px-4 py-2.5 border-b border-gray-200 dark:border-gray-700 text-sm last:border-b-0';
            entry.innerHTML =
                '<span class="text-gray-500 dark:text-gray-400">"' + transcript + '"</span>' +
                '<span class="mx-2 text-gray-300 dark:text-gray-600">&rarr;</span>' +
                '<span class="font-medium text-gray-800 dark:text-gray-200">' + action + '</span>';
            history.prepend(entry);

            // Keep max 10 entries
            while (history.children.length > 10) {
                history.removeChild(history.lastChild);
            }
        }

        // =====================================================
        // Temperature Display
        // =====================================================
        function updateTemperatureDisplay() {
            const tempEl = document.getElementById('current-temp');
            const updatedEl = document.getElementById('temp-updated');
            const espSpeedEl = document.getElementById('esp-speed');

            if (currentTemperature !== null) {
                tempEl.innerHTML = parseFloat(currentTemperature).toFixed(1) + ' &deg;C';

                // Determine which threshold bracket we're in
                let espSpeed = 0;
                let activeRow = 'threshold-0';

                if (currentTemperature >= 30) {
                    espSpeed = 100;
                    activeRow = 'threshold-100';
                } else if (currentTemperature >= 26) {
                    espSpeed = 50;
                    activeRow = 'threshold-50';
                } else if (currentTemperature >= 23) {
                    espSpeed = 25;
                    activeRow = 'threshold-25';
                } else {
                    espSpeed = 0;
                    activeRow = 'threshold-0';
                }

                espSpeedEl.textContent = espSpeed + '%';

                // Highlight active threshold row
                document.querySelectorAll('tr[id^="threshold-"]').forEach(function (row) {
                    if (row.id === activeRow) {
                        row.className = 'border-t border-blue-200 dark:border-blue-800 bg-blue-50 dark:bg-blue-900/20';
                        row.querySelector('.threshold-dot').className = 'threshold-dot w-3 h-3 rounded-full bg-blue-500 inline-block animate-pulse';
                    } else {
                        row.className = 'border-t border-gray-200 dark:border-gray-700';
                        row.querySelector('.threshold-dot').className = 'threshold-dot w-3 h-3 rounded-full bg-gray-300 dark:bg-gray-600 inline-block';
                    }
                });

                // Color the temperature based on value
                if (currentTemperature >= 30) {
                    tempEl.className = 'text-5xl font-bold text-red-500';
                } else if (currentTemperature >= 26) {
                    tempEl.className = 'text-5xl font-bold text-orange-500';
                } else if (currentTemperature >= 23) {
                    tempEl.className = 'text-5xl font-bold text-yellow-500';
                } else {
                    tempEl.className = 'text-5xl font-bold text-blue-500';
                }
            } else {
                tempEl.innerHTML = '-- &deg;C';
                tempEl.className = 'text-5xl font-bold text-gray-400';
                espSpeedEl.textContent = '--';
            }

            // Relative time for "last updated"
            if (temperatureUpdatedAt) {
                const diff = Math.floor((Date.now() - new Date(temperatureUpdatedAt).getTime()) / 1000);
                if (diff < 5) {
                    updatedEl.textContent = 'Updated just now';
                } else if (diff < 60) {
                    updatedEl.textContent = 'Updated ' + diff + ' seconds ago';
                } else if (diff < 3600) {
                    updatedEl.textContent = 'Updated ' + Math.floor(diff / 60) + ' minutes ago';
                } else {
                    updatedEl.textContent = 'Last updated over an hour ago';
                    updatedEl.className = 'text-sm text-red-500';
                }
            } else {
                updatedEl.textContent = 'Waiting for ESP32 data...';
            }
        }

        // Update relative timestamps every second
        setInterval(updateTemperatureDisplay, 1000);

        // =====================================================
        // Initialize
        // =====================================================
        initSpeechRecognition();
    </script>

</body>
</html>
