@extends('layouts.app')
@section('title', 'Voice Control — IoT Monitor')

@section('content')
{{-- ═══ Custom animations ═══ --}}
<style>
   @keyframes vc-ripple {
      0% {
         transform: scale(0.8);
         opacity: 0.6;
      }

      100% {
         transform: scale(2.2);
         opacity: 0;
      }
   }

   @keyframes vc-pulse-ring {
      0% {
         box-shadow: 0 0 0 0 rgba(0, 0, 0, 0.25);
      }

      70% {
         box-shadow: 0 0 0 14px rgba(0, 0, 0, 0);
      }

      100% {
         box-shadow: 0 0 0 0 rgba(0, 0, 0, 0);
      }
   }

   @keyframes vc-waveform {

      0%,
      100% {
         height: 10px;
      }

      50% {
         height: 28px;
      }
   }

   @keyframes vc-fade-in {
      from {
         opacity: 0;
         transform: translateY(6px);
      }

      to {
         opacity: 1;
         transform: translateY(0);
      }
   }

   @keyframes vc-spin-slow {
      to {
         transform: rotate(360deg);
      }
   }

   @keyframes vc-check {
      0% {
         stroke-dashoffset: 24;
      }

      100% {
         stroke-dashoffset: 0;
      }
   }

   .vc-ripple-1,
   .vc-ripple-2,
   .vc-ripple-3 {
      position: absolute;
      inset: -6px;
      border-radius: 9999px;
      border: 2px solid black;
      opacity: 0;
      pointer-events: none;
   }

   .vc-listening .vc-ripple-1 {
      animation: vc-ripple 1.8s ease-out infinite;
   }

   .vc-listening .vc-ripple-2 {
      animation: vc-ripple 1.8s ease-out 0.4s infinite;
   }

   .vc-listening .vc-ripple-3 {
      animation: vc-ripple 1.8s ease-out 0.8s infinite;
   }

   .vc-listening {
      animation: vc-pulse-ring 1.5s ease-out infinite;
   }

   .vc-wave-bar {
      width: 4px;
      border-radius: 9999px;
      background: black;
      transition: height 0.15s;
   }

   .vc-listening-active .vc-wave-bar {
      animation: vc-waveform 0.6s ease-in-out infinite;
   }

   .vc-listening-active .vc-wave-bar:nth-child(2) {
      animation-delay: 0.1s;
   }

   .vc-listening-active .vc-wave-bar:nth-child(3) {
      animation-delay: 0.2s;
   }

   .vc-listening-active .vc-wave-bar:nth-child(4) {
      animation-delay: 0.3s;
   }

   .vc-listening-active .vc-wave-bar:nth-child(5) {
      animation-delay: 0.15s;
   }

   .vc-cmd-card {
      animation: vc-fade-in 0.3s ease-out both;
   }

   .vc-cmd-card:nth-child(2) {
      animation-delay: 0.05s;
   }

   .vc-cmd-card:nth-child(3) {
      animation-delay: 0.1s;
   }

   .vc-cmd-card:nth-child(4) {
      animation-delay: 0.15s;
   }

   .vc-processing-spin {
      animation: vc-spin-slow 1s linear infinite;
   }

   .vc-result-icon {
      stroke-dasharray: 24;
      stroke-dashoffset: 24;
      animation: vc-check 0.4s ease-out forwards;
   }
</style>

{{-- ═══ Page Header ═══ --}}
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
   <div>
      <h1 class="text-xl font-semibold text-black">Voice Control</h1>
      <p class="text-xs text-neutral-400 mt-1">Control your IoT devices using voice commands or click to execute</p>
   </div>
   <div class="flex items-center flex-wrap gap-2">
      {{-- Hands-Free Toggle --}}
      <label class="flex items-center gap-2 px-3 py-1.5 border border-neutral-200 rounded-full cursor-pointer select-none hover:border-neutral-300 transition-colors">
         <input type="checkbox" id="handsfree-toggle" onchange="toggleHandsFreeMode()" class="sr-only peer">
         <div class="w-7 h-4 bg-neutral-300 rounded-full peer peer-checked:after:translate-x-3 peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-3 after:w-3 after:transition-all peer-checked:bg-black relative"></div>
         <span class="text-[11px] text-neutral-500 peer-checked:text-black transition-colors">Hands-Free</span>
      </label>
      {{-- Audio Feedback Toggle --}}
      <label class="flex items-center gap-2 px-3 py-1.5 border border-neutral-200 rounded-full cursor-pointer select-none hover:border-neutral-300 transition-colors">
         <input type="checkbox" id="audio-feedback-toggle" checked class="sr-only peer">
         <div class="w-7 h-4 bg-neutral-300 rounded-full peer peer-checked:after:translate-x-3 peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-3 after:w-3 after:transition-all peer-checked:bg-black relative"></div>
         <span class="text-[11px] text-neutral-500 peer-checked:text-black transition-colors">Audio</span>
      </label>
      {{-- Sinric Status --}}
      <div class="flex items-center space-x-2 px-3 py-1.5 border border-neutral-200 rounded-full" id="sinric-status">
         <span id="sinric-status-dot" class="w-2 h-2 rounded-full bg-neutral-300 transition-colors"></span>
         <span id="sinric-status-text" class="text-[11px] text-neutral-400">Checking...</span>
      </div>
   </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
   {{-- ═══ Left Column (2/3) ═══ --}}
   <div class="lg:col-span-2 space-y-6">

      {{-- ─── Hero Microphone Section ─── --}}
      <div class="border border-neutral-200 rounded-lg p-6 sm:p-8">
         <div class="flex flex-col items-center text-center">

            {{-- Mic Button (Large) --}}
            <div class="relative inline-flex items-center justify-center mb-5">
               <button id="mic-btn" onclick="toggleSpeechRecognition()"
                  class="relative z-10 flex items-center justify-center w-24 h-24 sm:w-28 sm:h-28 aspect-square rounded-full border-2 border-neutral-300 bg-white hover:border-black transition-all duration-300 group focus:outline-none"
                  aria-label="Click to speak a voice command">
                  {{-- Default mic icon --}}
                  <svg id="mic-icon"
                     class="w-10 h-10 sm:w-12 sm:h-12 text-neutral-400 group-hover:text-black transition-colors duration-200"
                     fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                     <path stroke-linecap="round" stroke-linejoin="round"
                        d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                  </svg>
                  {{-- Processing spinner (hidden by default) --}}
                  <svg id="mic-processing" class="w-10 h-10 sm:w-12 sm:h-12 text-black hidden vc-processing-spin"
                     fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                     <path stroke-linecap="round" stroke-linejoin="round"
                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                  </svg>
                  {{-- Success icon (hidden by default) --}}
                  <svg id="mic-success" class="w-10 h-10 sm:w-12 sm:h-12 text-black hidden" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                     <path class="vc-result-icon" stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                  </svg>
                  {{-- Error icon (hidden by default) --}}
                  <svg id="mic-error" class="w-10 h-10 sm:w-12 sm:h-12 text-neutral-600 hidden" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                     <path class="vc-result-icon" stroke-linecap="round" stroke-linejoin="round"
                        d="M6 18L18 6M6 6l12 12" />
                  </svg>
               </button>
               {{-- Animated ripple rings --}}
               <span class="vc-ripple-1"></span>
               <span class="vc-ripple-2"></span>
               <span class="vc-ripple-3"></span>
            </div>

            {{-- Waveform visualizer --}}
            <div id="vc-waveform" class="flex items-center justify-center space-x-1.5 h-8 mb-3">
               <span class="vc-wave-bar" style="height:10px"></span>
               <span class="vc-wave-bar" style="height:14px"></span>
               <span class="vc-wave-bar" style="height:10px"></span>
               <span class="vc-wave-bar" style="height:14px"></span>
               <span class="vc-wave-bar" style="height:10px"></span>
            </div>

            {{-- Status --}}
            <p id="mic-status" class="text-sm font-medium text-neutral-500 transition-colors">Click the microphone to
               start</p>
            <p id="mic-transcript" class="text-sm text-black font-medium mt-2 min-h-[1.5rem] max-w-md"></p>
            <div id="mic-unsupported" class="hidden text-xs text-neutral-500 mt-2 bg-neutral-50 px-3 py-2 rounded">
               Speech recognition is not supported in this browser. Please use Chrome or Edge.
            </div>
         </div>
      </div>

      {{-- ─── Available Commands (Categorized) ─── --}}
      <div class="border border-neutral-200 rounded-lg p-5">
         <div class="flex items-center justify-between mb-5">
            <div class="flex items-center space-x-2">
               <svg class="w-4 h-4 text-black" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
               </svg>
               <h3 class="text-sm font-semibold">Available Commands</h3>
            </div>
            <p class="text-[10px] text-neutral-400">Click any command to execute</p>
         </div>

         {{-- Category: Fan Controls --}}
         <div class="mb-5">
            <div class="flex items-center space-x-2 mb-3">
               <div class="w-6 h-6 rounded bg-neutral-100 flex items-center justify-center">
                  <svg class="w-3.5 h-3.5 text-black" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                     stroke-width="2">
                     <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 12c0-2.5-2-4.5-4.5-4.5S3 7 3 9s2.5 3 4.5 3H12z M12 12c2.5 0 4.5-2 4.5-4.5S17 3 15 3s-3 2.5-3 4.5V12z M12 12c0 2.5 2 4.5 4.5 4.5S21 17 21 15s-2.5-3-4.5-3H12z M12 12c-2.5 0-4.5 2-4.5 4.5S7 21 9 21s3-2.5 3-4.5V12z" />
                     <circle cx="12" cy="12" r="1" fill="currentColor" />
                  </svg>
               </div>
               <h4 class="text-xs font-semibold uppercase tracking-wider text-neutral-500">Fan Controls</h4>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
               <div onclick="executeVoiceCommand('turn_on', 'fan')"
                  class="vc-cmd-card group flex items-center gap-3 px-4 py-3 rounded-lg border border-neutral-100 hover:border-black hover:shadow-sm cursor-pointer transition-all duration-200">
                  <div
                     class="w-8 h-8 rounded-full bg-neutral-50 group-hover:bg-black flex items-center justify-center transition-colors shrink-0">
                     <svg class="w-4 h-4 text-neutral-400 group-hover:text-white transition-colors" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                     </svg>
                  </div>
                  <div class="flex-1 min-w-0">
                     <p class="text-xs font-medium text-black">Turn On Fan</p>
                     <p class="text-[10px] text-neutral-400 mt-0.5 truncate">"Hey Gout, turn on the fan"</p>
                  </div>
                  <svg class="w-3.5 h-3.5 text-neutral-300 group-hover:text-black shrink-0 transition-colors"
                     fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                     <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                  </svg>
               </div>
               <div onclick="executeVoiceCommand('turn_off', 'fan')"
                  class="vc-cmd-card group flex items-center gap-3 px-4 py-3 rounded-lg border border-neutral-100 hover:border-black hover:shadow-sm cursor-pointer transition-all duration-200">
                  <div
                     class="w-8 h-8 rounded-full bg-neutral-50 group-hover:bg-black flex items-center justify-center transition-colors shrink-0">
                     <svg class="w-4 h-4 text-neutral-400 group-hover:text-white transition-colors" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.93 4.93l14.14 14.14" />
                     </svg>
                  </div>
                  <div class="flex-1 min-w-0">
                     <p class="text-xs font-medium text-black">Turn Off Fan</p>
                     <p class="text-[10px] text-neutral-400 mt-0.5 truncate">"Hey Fan, turn off the fan"</p>
                  </div>
                  <svg class="w-3.5 h-3.5 text-neutral-300 group-hover:text-black shrink-0 transition-colors"
                     fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                     <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                  </svg>
               </div>
               <div onclick="executeVoiceCommand('set_speed', 'fan', 50)"
                  class="vc-cmd-card group flex items-center gap-3 px-4 py-3 rounded-lg border border-neutral-100 hover:border-black hover:shadow-sm cursor-pointer transition-all duration-200">
                  <div
                     class="w-8 h-8 rounded-full bg-neutral-50 group-hover:bg-black flex items-center justify-center transition-colors shrink-0">
                     <svg class="w-4 h-4 text-neutral-400 group-hover:text-white transition-colors" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                     </svg>
                  </div>
                  <div class="flex-1 min-w-0">
                     <p class="text-xs font-medium text-black">Set Fan Speed</p>
                     <p class="text-[10px] text-neutral-400 mt-0.5 truncate">"Hey Fan, set speed to 50%"</p>
                  </div>
                  <svg class="w-3.5 h-3.5 text-neutral-300 group-hover:text-black shrink-0 transition-colors"
                     fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                     <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                  </svg>
               </div>
            </div>
         </div>

         {{-- Category: Sensor Data --}}
         <div class="mb-5">
            <div class="flex items-center space-x-2 mb-3">
               <div class="w-6 h-6 rounded bg-neutral-100 flex items-center justify-center">
                  <svg class="w-3.5 h-3.5 text-black" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                     stroke-width="2">
                     <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                  </svg>
               </div>
               <h4 class="text-xs font-semibold uppercase tracking-wider text-neutral-500">Sensor Data</h4>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
               <div onclick="executeVoiceCommand('get_temperature', 'sensor')"
                  class="vc-cmd-card group flex items-center gap-3 px-4 py-3 rounded-lg border border-neutral-100 hover:border-black hover:shadow-sm cursor-pointer transition-all duration-200">
                  <div
                     class="w-8 h-8 rounded-full bg-neutral-50 group-hover:bg-black flex items-center justify-center transition-colors shrink-0">
                     <svg class="w-4 h-4 text-neutral-400 group-hover:text-white transition-colors" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                           d="M12 3v13.5M12 3a2.5 2.5 0 00-2.5 2.5v8.036a4 4 0 104.999 0V5.5A2.5 2.5 0 0012 3z" />
                     </svg>
                  </div>
                  <div class="flex-1 min-w-0">
                     <p class="text-xs font-medium text-black">Get Temperature</p>
                     <p class="text-[10px] text-neutral-400 mt-0.5 truncate">"Hey Fan, what's the temperature?"</p>
                  </div>
                  <svg class="w-3.5 h-3.5 text-neutral-300 group-hover:text-black shrink-0 transition-colors"
                     fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                     <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                  </svg>
               </div>
               <div onclick="executeVoiceCommand('get_humidity', 'sensor')"
                  class="vc-cmd-card group flex items-center gap-3 px-4 py-3 rounded-lg border border-neutral-100 hover:border-black hover:shadow-sm cursor-pointer transition-all duration-200">
                  <div
                     class="w-8 h-8 rounded-full bg-neutral-50 group-hover:bg-black flex items-center justify-center transition-colors shrink-0">
                     <svg class="w-4 h-4 text-neutral-400 group-hover:text-white transition-colors" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                           d="M12 21a8 8 0 01-8-8c0-4 8-12 8-12s8 8 8 12a8 8 0 01-8 8z" />
                     </svg>
                  </div>
                  <div class="flex-1 min-w-0">
                     <p class="text-xs font-medium text-black">Get Humidity</p>
                     <p class="text-[10px] text-neutral-400 mt-0.5 truncate">"Hey Fan, what's the humidity?"</p>
                  </div>
                  <svg class="w-3.5 h-3.5 text-neutral-300 group-hover:text-black shrink-0 transition-colors"
                     fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                     <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                  </svg>
               </div>
            </div>
         </div>

         {{-- Category: System --}}
         <div>
            <div class="flex items-center space-x-2 mb-3">
               <div class="w-6 h-6 rounded bg-neutral-100 flex items-center justify-center">
                  <svg class="w-3.5 h-3.5 text-black" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                     stroke-width="2">
                     <path stroke-linecap="round" stroke-linejoin="round"
                        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                     <circle cx="12" cy="12" r="3" />
                  </svg>
               </div>
               <h4 class="text-xs font-semibold uppercase tracking-wider text-neutral-500">System</h4>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
               <div onclick="executeVoiceCommand('toggle_all', 'all')"
                  class="vc-cmd-card group flex items-center gap-3 px-4 py-3 rounded-lg border border-neutral-100 hover:border-black hover:shadow-sm cursor-pointer transition-all duration-200">
                  <div
                     class="w-8 h-8 rounded-full bg-neutral-50 group-hover:bg-black flex items-center justify-center transition-colors shrink-0">
                     <svg class="w-4 h-4 text-neutral-400 group-hover:text-white transition-colors" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                           d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                     </svg>
                  </div>
                  <div class="flex-1 min-w-0">
                     <p class="text-xs font-medium text-black">Toggle All Devices</p>
                     <p class="text-[10px] text-neutral-400 mt-0.5 truncate">"Hey Fan, toggle all devices"</p>
                  </div>
                  <svg class="w-3.5 h-3.5 text-neutral-300 group-hover:text-black shrink-0 transition-colors"
                     fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                     <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                  </svg>
               </div>
            </div>
         </div>
      </div>

      {{-- ─── Custom Command ─── --}}
      <div class="border border-neutral-200 rounded-lg p-5">
         <div class="flex items-center space-x-2 mb-4">
            <svg class="w-4 h-4 text-black" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
               <path stroke-linecap="round" stroke-linejoin="round"
                  d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
            <h3 class="text-sm font-semibold">Custom Command</h3>
         </div>
         <div class="flex flex-col sm:flex-row sm:items-end gap-3">
            <div class="flex-1">
               <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                  <div>
                     <label class="block text-[10px] text-neutral-500 font-medium mb-1.5">Action</label>
                     <select id="custom-action"
                        class="w-full border border-neutral-200 rounded-lg px-3 py-2 text-xs focus:border-black focus:ring-0 focus:outline-none bg-white transition">
                        <option value="turn_on">Turn On</option>
                        <option value="turn_off">Turn Off</option>
                        <option value="set_speed">Set Speed</option>
                        <option value="get_temperature">Get Temperature</option>
                        <option value="get_humidity">Get Humidity</option>
                        <option value="toggle_all">Toggle All</option>
                     </select>
                  </div>
                  <div>
                     <label class="block text-[10px] text-neutral-500 font-medium mb-1.5">Device</label>
                     <select id="custom-device"
                        class="w-full border border-neutral-200 rounded-lg px-3 py-2 text-xs focus:border-black focus:ring-0 focus:outline-none bg-white transition">
                        <option value="fan">Fan</option>
                        <option value="sensor">Sensor</option>
                        <option value="all">All Devices</option>
                     </select>
                  </div>
                  <div>
                     <label class="block text-[10px] text-neutral-500 font-medium mb-1.5">Value <span
                           class="text-neutral-400">(optional)</span></label>
                     <input id="custom-value" type="number" min="0" max="100" placeholder="e.g. 75"
                        class="w-full border border-neutral-200 rounded-lg px-3 py-2 text-xs focus:border-black focus:ring-0 focus:outline-none transition">
                  </div>
               </div>
            </div>
            <button onclick="sendCustomCommand()"
               class="px-5 py-2 bg-black text-white text-xs font-medium rounded-lg hover:bg-neutral-800 transition-colors whitespace-nowrap">
               Execute
            </button>
         </div>
      </div>

      {{-- ─── Command Log ─── --}}
      <div class="border border-neutral-200 rounded-lg p-5">
         <div class="flex items-center justify-between mb-3">
            <div class="flex items-center space-x-2">
               <svg class="w-4 h-4 text-black" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round"
                     d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
               </svg>
               <h3 class="text-sm font-semibold">Command Log</h3>
            </div>
            <button onclick="clearCommandLog()"
               class="text-[10px] text-neutral-400 hover:text-black px-2 py-1 rounded border border-neutral-200 hover:border-black transition">Clear</button>
         </div>
         <div id="voice-command-log"
            class="bg-neutral-50 rounded-lg p-4 max-h-64 overflow-y-auto font-mono text-[11px] text-neutral-600 space-y-1.5 scroll-smooth">
            <p class="text-neutral-400 italic">No commands executed yet.</p>
         </div>
      </div>
   </div>

   {{-- ═══ Right Column (1/3) ═══ --}}
   <div class="space-y-6">

      {{-- ─── How It Works ─── --}}
      <div class="border border-neutral-200 rounded-lg p-5">
         <h3 class="text-sm font-semibold mb-4">How It Works</h3>
         <div class="space-y-4">
            <div class="flex items-start gap-3">
               <div
                  class="w-6 h-6 rounded-full bg-black text-white flex items-center justify-center text-[10px] font-bold shrink-0 mt-0.5">
                  1</div>
               <div>
                  <p class="text-xs font-medium text-black">Click the microphone</p>
                  <p class="text-[10px] text-neutral-400 mt-0.5">Or enable Hands-Free mode for continuous listening</p>
               </div>
            </div>
            <div class="flex items-start gap-3">
               <div
                  class="w-6 h-6 rounded-full bg-black text-white flex items-center justify-center text-[10px] font-bold shrink-0 mt-0.5">
                  2</div>
               <div>
                  <p class="text-xs font-medium text-black">Say your command</p>
                  <p class="text-[10px] text-neutral-400 mt-0.5">Use wake word "Hey Fan" + command in hands-free mode
                  </p>
               </div>
            </div>
            <div class="flex items-start gap-3">
               <div
                  class="w-6 h-6 rounded-full bg-black text-white flex items-center justify-center text-[10px] font-bold shrink-0 mt-0.5">
                  3</div>
               <div>
                  <p class="text-xs font-medium text-black">See instant feedback</p>
                  <p class="text-[10px] text-neutral-400 mt-0.5">Visual and audio confirmation of your command</p>
               </div>
            </div>
         </div>
         <div class="mt-4 pt-3 border-t border-neutral-100">
            <p class="text-[10px] text-neutral-500 font-medium mb-2">Example phrases:</p>
            <div class="space-y-1.5">
               <p class="text-[10px] text-neutral-400 bg-neutral-50 rounded px-2.5 py-1.5">"Turn on the fan"</p>
               <p class="text-[10px] text-neutral-400 bg-neutral-50 rounded px-2.5 py-1.5">"Set fan speed to 75 percent"
               </p>
               <p class="text-[10px] text-neutral-400 bg-neutral-50 rounded px-2.5 py-1.5">"What's the temperature?"</p>
            </div>
         </div>
      </div>

      {{-- ─── Quick Actions ─── --}}
      <div class="border border-neutral-200 rounded-lg p-5">
         <h3 class="text-sm font-semibold mb-3">Quick Actions</h3>
         <div class="space-y-2">
            <button onclick="executeVoiceCommand('turn_on', 'fan')"
               class="w-full text-left flex items-center gap-3 px-3 py-2.5 border border-neutral-200 rounded-lg hover:border-black transition text-xs group">
               <div
                  class="w-6 h-6 rounded bg-neutral-50 group-hover:bg-black flex items-center justify-center transition-colors">
                  <svg class="w-3 h-3 text-neutral-400 group-hover:text-white transition-colors" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                     <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                  </svg>
               </div>
               <span class="flex-1">Turn Fan On</span>
               <svg class="w-3 h-3 text-neutral-300 group-hover:text-black transition-colors" fill="none"
                  viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
               </svg>
            </button>
            <button onclick="executeVoiceCommand('turn_off', 'fan')"
               class="w-full text-left flex items-center gap-3 px-3 py-2.5 border border-neutral-200 rounded-lg hover:border-black transition text-xs group">
               <div
                  class="w-6 h-6 rounded bg-neutral-50 group-hover:bg-black flex items-center justify-center transition-colors">
                  <svg class="w-3 h-3 text-neutral-400 group-hover:text-white transition-colors" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                     <circle cx="12" cy="12" r="10" />
                     <path stroke-linecap="round" stroke-linejoin="round" d="M4.93 4.93l14.14 14.14" />
                  </svg>
               </div>
               <span class="flex-1">Turn Fan Off</span>
               <svg class="w-3 h-3 text-neutral-300 group-hover:text-black transition-colors" fill="none"
                  viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
               </svg>
            </button>
            <button onclick="executeVoiceCommand('get_temperature', 'sensor')"
               class="w-full text-left flex items-center gap-3 px-3 py-2.5 border border-neutral-200 rounded-lg hover:border-black transition text-xs group">
               <div
                  class="w-6 h-6 rounded bg-neutral-50 group-hover:bg-black flex items-center justify-center transition-colors">
                  <svg class="w-3 h-3 text-neutral-400 group-hover:text-white transition-colors" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                     <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 3v13.5M12 3a2.5 2.5 0 00-2.5 2.5v8.036a4 4 0 104.999 0V5.5A2.5 2.5 0 0012 3z" />
                  </svg>
               </div>
               <span class="flex-1">Get Temperature</span>
               <svg class="w-3 h-3 text-neutral-300 group-hover:text-black transition-colors" fill="none"
                  viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
               </svg>
            </button>
            <button onclick="executeVoiceCommand('get_humidity', 'sensor')"
               class="w-full text-left flex items-center gap-3 px-3 py-2.5 border border-neutral-200 rounded-lg hover:border-black transition text-xs group">
               <div
                  class="w-6 h-6 rounded bg-neutral-50 group-hover:bg-black flex items-center justify-center transition-colors">
                  <svg class="w-3 h-3 text-neutral-400 group-hover:text-white transition-colors" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                     <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 21a8 8 0 01-8-8c0-4 8-12 8-12s8 8 8 12a8 8 0 01-8 8z" />
                  </svg>
               </div>
               <span class="flex-1">Get Humidity</span>
               <svg class="w-3 h-3 text-neutral-300 group-hover:text-black transition-colors" fill="none"
                  viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
               </svg>
            </button>
            <button onclick="executeVoiceCommand('toggle_all', 'all')"
               class="w-full text-left flex items-center gap-3 px-3 py-2.5 border border-neutral-200 rounded-lg hover:border-black transition text-xs group">
               <div
                  class="w-6 h-6 rounded bg-neutral-50 group-hover:bg-black flex items-center justify-center transition-colors">
                  <svg class="w-3 h-3 text-neutral-400 group-hover:text-white transition-colors" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                     <path stroke-linecap="round" stroke-linejoin="round"
                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                  </svg>
               </div>
               <span class="flex-1">Toggle All Devices</span>
               <svg class="w-3 h-3 text-neutral-300 group-hover:text-black transition-colors" fill="none"
                  viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
               </svg>
            </button>
         </div>
      </div>

      {{-- ─── Current Fan Status ─── --}}
      <div class="border border-neutral-200 rounded-lg p-5">
         <h3 class="text-sm font-semibold mb-4">Current Fan Status</h3>
         <div id="fan-status-box" class="space-y-3">
            <div class="flex items-center justify-between p-3 bg-neutral-50 rounded-lg">
               <div class="flex items-center gap-2.5">
                  <svg id="vc-fan-icon" class="w-5 h-5 text-neutral-300 transition-all duration-300" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                     <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 12c0-2.5-2-4.5-4.5-4.5S3 7 3 9s2.5 3 4.5 3H12z M12 12c2.5 0 4.5-2 4.5-4.5S17 3 15 3s-3 2.5-3 4.5V12z M12 12c0 2.5 2 4.5 4.5 4.5S21 17 21 15s-2.5-3-4.5-3H12z M12 12c-2.5 0-4.5 2-4.5 4.5S7 21 9 21s3-2.5 3-4.5V12z" />
                     <circle cx="12" cy="12" r="1" fill="currentColor" />
                  </svg>
                  <span class="text-xs text-neutral-500">Power</span>
               </div>
               <span id="vc-fan-status"
                  class="text-xs font-semibold text-neutral-400 px-2 py-0.5 rounded border border-neutral-200">Loading...</span>
            </div>
            <div class="flex items-center justify-between p-3 bg-neutral-50 rounded-lg">
               <div class="flex items-center gap-2.5">
                  <svg class="w-5 h-5 text-neutral-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                     stroke-width="1.5">
                     <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                  </svg>
                  <span class="text-xs text-neutral-500">Speed</span>
               </div>
               <span id="vc-fan-speed" class="text-xs font-semibold text-neutral-400">--</span>
            </div>
         </div>
      </div>
   </div>
</div>
@endsection

@push('scripts')
<script>
   // ─── Speech Recognition (Web Speech API) ───
   let recognition = null;
   let isListening = false;
   let isHandsFree = false;
   let speechSynth = window.speechSynthesis;
   let commandTimeout = null;

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
         updateMicUI('listening');
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
         updateMicUI('idle');
      };

      recognition.onend = () => {
         isListening = false;
         if (isHandsFree) {
            updateMicUI('handsfree');
            setTimeout(() => {
               if (isHandsFree && !isListening) {
                  try {
                     recognition.start();
                  } catch (e) {}
               }
            }, 500);
            setMicStatus('Hands-free active — say "Hey Fan" + command', 'text-black');
         } else {
            updateMicUI('idle');
            setMicStatus('Click the microphone to start', 'text-neutral-500');
         }
      };

      return true;
   }

   function toggleSpeechRecognition() {
      if (!recognition) return;
      if (isListening) {
         recognition.abort();
         isListening = false;
         updateMicUI('idle');
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
         updateMicUI('handsfree');
         if (!isListening) {
            try {
               recognition.start();
            } catch (e) {}
         }
      } else {
         recognition.continuous = false;
         if (isListening) recognition.abort();
         setMicStatus('Click the microphone to start', 'text-neutral-500');
         speakFeedback('Hands free mode deactivated.');
         logCommand('Hands-free mode disabled', 'info');
         updateMicUI('idle');
      }
   }

   function updateMicUI(state) {
      const btn = document.getElementById('mic-btn');
      const icon = document.getElementById('mic-icon');
      const processing = document.getElementById('mic-processing');
      const success = document.getElementById('mic-success');
      const error = document.getElementById('mic-error');
      const waveform = document.getElementById('vc-waveform');

      // Reset all
      [icon, processing, success, error].forEach(el => el.classList.add('hidden'));
      btn.classList.remove('border-black', 'bg-black', 'border-neutral-300', 'bg-white', 'vc-listening');
      btn.parentElement.querySelectorAll('.vc-ripple-1, .vc-ripple-2, .vc-ripple-3').forEach(r => r.style.display =
         'none');
      waveform.classList.remove('vc-listening-active');

      if (state === 'listening' || state === 'handsfree') {
         btn.classList.add('border-black', 'bg-black', 'vc-listening');
         icon.classList.remove('hidden');
         icon.classList.remove('text-neutral-400');
         icon.classList.add('text-white');
         btn.parentElement.querySelectorAll('.vc-ripple-1, .vc-ripple-2, .vc-ripple-3').forEach(r => r.style.display = '');
         waveform.classList.add('vc-listening-active');
      } else if (state === 'processing') {
         btn.classList.add('border-black', 'bg-white');
         processing.classList.remove('hidden');
      } else if (state === 'success') {
         btn.classList.add('border-black', 'bg-white');
         success.classList.remove('hidden');
      } else if (state === 'error') {
         btn.classList.add('border-neutral-300', 'bg-white');
         error.classList.remove('hidden');
      } else {
         btn.classList.add('border-neutral-300', 'bg-white');
         icon.classList.remove('hidden');
         icon.classList.remove('text-white');
         icon.classList.add('text-neutral-400');
      }
   }

   function setMicStatus(text, colorClass) {
      const el = document.getElementById('mic-status');
      el.textContent = text;
      el.className = `text-sm font-medium transition-colors ${colorClass}`;
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
         updateMicUI('error');
         setMicStatus(`Didn't understand: "${command}"`, 'text-neutral-500');
         speakFeedback(`Sorry, I didn't understand. Try saying turn on the fan, or set speed to 50 percent.`);
         logCommand(`Unrecognized: "${command}"`, 'error');
         clearTimeout(commandTimeout);
         commandTimeout = setTimeout(() => {
            if (!isListening && !isHandsFree) updateMicUI('idle');
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
      const prefixMap = {
         success: '✓',
         error: '✗',
         info: '→'
      };
      const colorMap = {
         success: 'text-black font-medium',
         error: 'text-neutral-900 font-semibold',
         info: ''
      };
      const prefix = prefixMap[type] || '→';
      const el = document.createElement('p');
      el.className = colorMap[type] || '';
      el.textContent = `[${time}] ${prefix} ${message}`;
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
            text.className = 'text-[11px] text-black font-medium';
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
      updateMicUI('processing');

      try {
         const res = await axios.post('/api/sinric/command', {
            action,
            device,
            value
         });
         if (res.data.success) {
            logCommand(res.data.message || `${description} — OK`, 'success');
            updateMicUI('success');
            fetchFanStatus();
         } else {
            logCommand(res.data.message || `Failed: ${description}`, 'error');
            updateMicUI('error');
         }
      } catch (e) {
         const msg = e.response?.data?.message || e.message || 'Request failed';
         logCommand(`Error: ${msg}`, 'error');
         updateMicUI('error');
      }

      clearTimeout(commandTimeout);
      commandTimeout = setTimeout(() => {
         if (!isListening) {
            updateMicUI(isHandsFree ? 'handsfree' : 'idle');
            if (!isHandsFree) {
               setMicStatus('Click the microphone to start', 'text-neutral-500');
            }
         }
      }, 2500);
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
         const isOn = res.data.status === 'on';
         document.getElementById('vc-fan-status').textContent = isOn ? 'ON' : 'OFF';
         document.getElementById('vc-fan-status').className = 'text-xs font-semibold px-2 py-0.5 rounded border ' + (
            isOn ?
            'border-black text-black' : 'border-neutral-200 text-neutral-400');
         document.getElementById('vc-fan-speed').textContent = res.data.speed + ' / 255';
         const fanIcon = document.getElementById('vc-fan-icon');
         if (fanIcon) {
            fanIcon.className = 'w-5 h-5 transition-all duration-300 ' + (isOn ? 'text-black animate-spin' :
               'text-neutral-300');
         }
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