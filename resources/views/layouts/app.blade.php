<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <meta name="csrf-token" content="{{ csrf_token() }}">
   <title>@yield('title', 'Fans Dashboard')</title>
   @vite(['resources/css/app.css', 'resources/js/app.js'])
   <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
   <style>
      [x-cloak] {
         display: none !important;
      }
   </style>
</head>

<body class="bg-white min-h-screen font-sans text-black">
   <div class="flex min-h-screen">

      @auth
      {{-- Sidebar --}}
      <aside id="sidebar"
         class="fixed inset-y-0 left-0 z-30 w-60 bg-black text-white transform -translate-x-full md:translate-x-0 transition-transform duration-200 ease-in-out flex flex-col">

         {{-- Logo --}}
         <div class="flex items-center space-x-3 px-5 h-14 border-b border-neutral-800 shrink-0">
            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
               <path stroke-linecap="round" stroke-linejoin="round"
                  d="M12 12c-2.5-2.5-5-3-7-1s-1.5 4.5 1 7m6-6c2.5-2.5 3-5 1-7s-4.5-1.5-7 1m6 6c2.5 2.5 5 3 7 1s1.5-4.5-1-7m-6 6c-2.5 2.5-3 5-1 7s4.5 1.5 7-1" />
               <circle cx="12" cy="12" r="1.5" fill="currentColor" />
            </svg>
            <span class="text-sm font-semibold tracking-wide uppercase">Fan Monitoring</span>
         </div>

         {{-- Navigation --}}
         <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-0.5" onclick="closeSidebarOnMobile()">
            <a href="{{ route('dashboard') }}"
               class="flex items-center space-x-3 px-3 py-2 rounded text-sm transition
                  {{ request()->routeIs('dashboard') ? 'bg-white text-black font-semibold' : 'text-neutral-400 hover:bg-neutral-900 hover:text-white' }}">
               <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round"
                     d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-4 0a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 01-1 1h-2z" />
               </svg>
               <span>Dashboard</span>
            </a>

            <a href="{{ route('devices.index') }}"
               class="flex items-center space-x-3 px-3 py-2 rounded text-sm transition
                  {{ request()->routeIs('devices.*') ? 'bg-white text-black font-semibold' : 'text-neutral-400 hover:bg-neutral-900 hover:text-white' }}">
               <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round"
                     d="M12 12c-2.5-2.5-5-3-7-1s-1.5 4.5 1 7m6-6c2.5-2.5 3-5 1-7s-4.5-1.5-7 1m6 6c2.5 2.5 5 3 7 1s1.5-4.5-1-7m-6 6c-2.5 2.5-3 5-1 7s4.5 1.5 7-1" />
                  <circle cx="12" cy="12" r="1" fill="currentColor" />
               </svg>
               <span>Devices</span>
            </a>

            <a href="{{ route('alerts.index') }}"
               class="flex items-center space-x-3 px-3 py-2 rounded text-sm transition
                  {{ request()->routeIs('alerts.*') ? 'bg-white text-black font-semibold' : 'text-neutral-400 hover:bg-neutral-900 hover:text-white' }}">
               <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round"
                     d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
               </svg>
               <span>Alerts</span>
               @if(isset($unreadAlertCount) && $unreadAlertCount > 0)
               <span
                  class="ml-auto bg-white text-black text-[10px] font-bold rounded-full h-4 w-4 flex items-center justify-center">
                  {{ $unreadAlertCount > 9 ? '9+' : $unreadAlertCount }}
               </span>
               @endif
            </a>

            <a href="{{ route('voice-control') }}"
               class="flex items-center space-x-3 px-3 py-2 rounded text-sm transition
                  {{ request()->routeIs('voice-control') ? 'bg-white text-black font-semibold' : 'text-neutral-400 hover:bg-neutral-900 hover:text-white' }}">
               <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round"
                     d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
               </svg>
               <span>Voice Control</span>
            </a>

            <a href="{{ route('temperature-control') }}"
               class="flex items-center space-x-3 px-3 py-2 rounded text-sm transition
                  {{ request()->routeIs('temperature-control') ? 'bg-white text-black font-semibold' : 'text-neutral-400 hover:bg-neutral-900 hover:text-white' }}">
               <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round"
                     d="M12 3v13.5M12 3a2.5 2.5 0 00-2.5 2.5v8.036a4 4 0 104.999 0V5.5A2.5 2.5 0 0012 3z" />
               </svg>
               <span>Temp Control</span>
            </a>

         </nav>

         {{-- User --}}
         <div class="shrink-0 border-t border-neutral-800 p-3">
            <div class="flex items-center space-x-2.5">
               <div class="w-8 h-8 rounded-full bg-white text-black flex items-center justify-center text-xs font-bold">
                  {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
               </div>
               <div class="flex-1 min-w-0">
                  <p class="text-xs font-medium truncate">{{ auth()->user()->name }}</p>
                  <p class="text-[10px] text-neutral-500 truncate">{{ auth()->user()->email }}</p>
               </div>
            </div>
            <form method="POST" action="{{ route('logout') }}" class="mt-2">
               @csrf
               <button type="submit"
                  class="w-full flex items-center justify-center space-x-1.5 px-3 py-1.5 rounded text-xs text-neutral-500 hover:bg-neutral-900 hover:text-white transition">
                  <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                     <path stroke-linecap="round" stroke-linejoin="round"
                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                  </svg>
                  <span>Log Out</span>
               </button>
            </form>
         </div>
      </aside>

      {{-- Mobile overlay --}}
      <div id="sidebar-overlay" class="fixed inset-0 z-20 bg-black/40 hidden md:hidden" onclick="toggleSidebar()"></div>
      @endauth

      {{-- Main --}}
      <div class="flex-1 flex flex-col @auth md:ml-60 @endauth">

         {{-- Top bar --}}
         <header
            class="sticky top-0 z-10 bg-white border-b border-neutral-200 h-12 flex items-center px-4 sm:px-6 shrink-0">
            @auth
            <button onclick="toggleSidebar()"
               class="md:hidden mr-3 p-1.5 rounded text-neutral-500 hover:text-black hover:bg-neutral-100">
               <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
               </svg>
            </button>
            @endauth

            <h1 class="text-sm font-semibold text-black">@yield('title', 'IoT Dashboard')</h1>

            <div class="ml-auto flex items-center space-x-3">
               @guest
               <a href="{{ route('login') }}" class="text-xs text-neutral-600 hover:text-black">Login</a>
               <a href="{{ route('register') }}"
                  class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded bg-black text-white hover:bg-neutral-800">
                  Register
               </a>
               @endguest
            </div>
         </header>

         {{-- Flash Messages --}}
         @if(session('success'))
         <div class="px-4 sm:px-6 mt-3">
            <div
               class="bg-neutral-100 border border-neutral-300 text-black px-4 py-2.5 rounded flex items-center justify-between text-sm">
               <span>{{ session('success') }}</span>
               <button onclick="this.parentElement.remove()"
                  class="text-neutral-400 hover:text-black text-lg ml-4">&times;</button>
            </div>
         </div>
         @endif

         @if($errors->any())
         <div class="px-4 sm:px-6 mt-3">
            <div class="bg-white border border-black text-black px-4 py-2.5 rounded text-sm">
               <ul class="list-disc list-inside space-y-0.5">
                  @foreach($errors->all() as $error)
                  <li>{{ $error }}</li>
                  @endforeach
               </ul>
            </div>
         </div>
         @endif

         {{-- Content --}}
         <main class="flex-1 px-4 sm:px-6 py-6">
            @yield('content')
         </main>

         {{-- Footer --}}
         <footer class="border-t border-neutral-200 shrink-0">
            <div class="px-4 sm:px-6 py-3 text-center text-[11px] text-neutral-400">
               &copy; {{ date('Y') }} IoT Monitor &middot; Laravel {{ app()->version() }}
            </div>
         </footer>
      </div>
   </div>

   <script>
      function toggleSidebar() {
         document.getElementById('sidebar').classList.toggle('-translate-x-full');
         document.getElementById('sidebar-overlay').classList.toggle('hidden');
      }

      function closeSidebarOnMobile() {
         if (window.innerWidth < 768) {
            document.getElementById('sidebar').classList.add('-translate-x-full');
            document.getElementById('sidebar-overlay').classList.add('hidden');
         }
      }
   </script>
   @stack('scripts')
</body>

</html>