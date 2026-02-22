<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'IoT Fan Control') }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
        <style>
            *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
            body {
                font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif;
                background-color: #FDFDFC;
                color: #1b1b18;
                min-height: 100vh;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                padding: 1.5rem;
            }
            a { text-decoration: none; color: inherit; }
            .container { width: 100%; max-width: 960px; }
            nav { display: flex; justify-content: flex-end; gap: 1rem; margin-bottom: 2rem; }
            .nav-link {
                display: inline-block; padding: 6px 20px; font-size: 0.875rem;
                border: 1px solid #19140035; border-radius: 4px; transition: border-color 0.15s;
            }
            .nav-link:hover { border-color: #1915014a; }
            .nav-link.ghost { border-color: transparent; }
            .nav-link.ghost:hover { border-color: #19140035; }
            .hero { display: flex; flex-direction: column; align-items: center; gap: 3rem; padding: 2rem 0; }
            @media (min-width: 768px) { .hero { flex-direction: row; align-items: center; } }
            .hero-text { flex: 1; }
            .hero-visual { flex: 1; display: flex; justify-content: center; }
            h1 { font-size: 2.25rem; font-weight: 600; line-height: 1.2; margin-bottom: 1rem; }
            .subtitle { font-size: 1rem; color: #706f6c; line-height: 1.6; margin-bottom: 1.5rem; }
            .features { list-style: none; margin-bottom: 2rem; }
            .features li { display: flex; align-items: center; gap: 0.5rem; padding: 0.4rem 0; font-size: 0.9rem; color: #706f6c; }
            .features li::before { content: ''; display: inline-block; width: 6px; height: 6px; background: #1b1b18; border-radius: 50%; flex-shrink: 0; }
            .badges { display: flex; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 2rem; }
            .badge {
                font-size: 0.75rem; padding: 4px 10px; border: 1px solid #e3e3e0;
                border-radius: 9999px; color: #706f6c; font-weight: 500;
            }
            .cta-group { display: flex; gap: 0.75rem; flex-wrap: wrap; }
            .btn {
                display: inline-block; padding: 10px 24px; font-size: 0.875rem; font-weight: 500;
                border-radius: 4px; border: 1px solid transparent; transition: all 0.15s; cursor: pointer;
            }
            .btn-primary { background: #1b1b18; color: #FDFDFC; border-color: #1b1b18; }
            .btn-primary:hover { background: #000; }
            .btn-secondary { background: transparent; color: #1b1b18; border-color: #19140035; }
            .btn-secondary:hover { border-color: #1b1b18; }
            @keyframes spin-fan { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
            @keyframes pulse-dot { 0%, 100% { opacity: 1; } 50% { opacity: 0.4; } }
            .fan-spin { animation: spin-fan 3s linear infinite; transform-origin: center; }
            .pulse { animation: pulse-dot 2s ease-in-out infinite; }
            @media (prefers-color-scheme: dark) {
                body { background-color: #0a0a0a; color: #EDEDEC; }
                .nav-link { border-color: #3E3E3A; color: #EDEDEC; }
                .nav-link:hover { border-color: #62605b; }
                .nav-link.ghost { border-color: transparent; }
                .nav-link.ghost:hover { border-color: #3E3E3A; }
                .subtitle, .features li { color: #A1A09A; }
                .badge { border-color: #3E3E3A; color: #A1A09A; }
                .btn-primary { background: #EDEDEC; color: #1b1b18; border-color: #EDEDEC; }
                .btn-primary:hover { background: #fff; }
                .btn-secondary { color: #EDEDEC; border-color: #3E3E3A; }
                .btn-secondary:hover { border-color: #EDEDEC; }
                h1 { color: #EDEDEC; }
                .features li::before { background: #EDEDEC; }
                svg text { fill: #EDEDEC; }
                svg rect[fill="#1b1b18"] { fill: #EDEDEC; }
                svg line[stroke="#1b1b18"] { stroke: #EDEDEC; }
                svg path[stroke="#1b1b18"] { stroke: #EDEDEC; }
                svg circle[fill="#1b1b18"] { fill: #EDEDEC; }
                svg path[fill="#1b1b18"] { fill: #EDEDEC; }
                svg rect[stroke="#e3e3e0"] { stroke: #3E3E3A; }
                svg circle[stroke="#e3e3e0"] { stroke: #3E3E3A; }
                svg line[stroke="#e3e3e0"] { stroke: #3E3E3A; }
                svg text[fill="#706f6c"] { fill: #A1A09A; }
                svg text[fill="#FDFDFC"] { fill: #1b1b18; }
            }
        </style>
    </head>
    <body>
        <div class="container">
            @if (Route::has('login'))
                <nav>
                    @auth
                        <a href="{{ url('/dashboard') }}" class="nav-link">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="nav-link ghost">Log in</a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="nav-link">Register</a>
                        @endif
                    @endauth
                </nav>
            @endif

            <div class="hero">
                <div class="hero-text">
                    <h1>IoT Fan Control System</h1>
                    <p class="subtitle">
                        Monitor temperature and humidity in real-time, control your fan remotely,
                        and automate your environment with smart rules and voice commands.
                    </p>
                    <ul class="features">
                        <li>Real-time temperature &amp; humidity monitoring</li>
                        <li>Remote fan control via web dashboard</li>
                        <li>Automated temperature profiles &amp; rules</li>
                        <li>Voice control with Google Assistant</li>
                        <li>Smart alerts &amp; notifications</li>
                    </ul>
                    <div class="badges">
                        <span class="badge">ESP32</span>
                        <span class="badge">Laravel</span>
                        <span class="badge">DHT Sensor</span>
                        <span class="badge">Sinric Pro</span>
                        <span class="badge">REST API</span>
                    </div>
                    <div class="cta-group">
                        @auth
                            <a href="{{ url('/dashboard') }}" class="btn btn-primary">Go to Dashboard</a>
                        @else
                            <a href="{{ route('login') }}" class="btn btn-primary">Get Started</a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="btn btn-secondary">Create Account</a>
                            @endif
                        @endauth
                    </div>
                </div>
                <div class="hero-visual">
                    <svg width="360" height="400" viewBox="0 0 360 400" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <!-- Background card -->
                        <rect x="30" y="20" width="300" height="360" rx="16" fill="none" stroke="#e3e3e0" stroke-width="1"/>
                        <!-- ESP32 Chip -->
                        <rect x="130" y="50" width="100" height="60" rx="8" fill="#1b1b18"/>
                        <text x="180" y="77" text-anchor="middle" fill="#FDFDFC" font-size="11" font-weight="600" font-family="'Instrument Sans', sans-serif">ESP32</text>
                        <text x="180" y="95" text-anchor="middle" fill="#706f6c" font-size="9" font-family="'Instrument Sans', sans-serif">WiFi + BLE</text>
                        <!-- Chip pins left -->
                        <line x1="115" y1="62" x2="130" y2="62" stroke="#1b1b18" stroke-width="2"/>
                        <line x1="115" y1="74" x2="130" y2="74" stroke="#1b1b18" stroke-width="2"/>
                        <line x1="115" y1="86" x2="130" y2="86" stroke="#1b1b18" stroke-width="2"/>
                        <line x1="115" y1="98" x2="130" y2="98" stroke="#1b1b18" stroke-width="2"/>
                        <!-- Chip pins right -->
                        <line x1="230" y1="62" x2="245" y2="62" stroke="#1b1b18" stroke-width="2"/>
                        <line x1="230" y1="74" x2="245" y2="74" stroke="#1b1b18" stroke-width="2"/>
                        <line x1="230" y1="86" x2="245" y2="86" stroke="#1b1b18" stroke-width="2"/>
                        <line x1="230" y1="98" x2="245" y2="98" stroke="#1b1b18" stroke-width="2"/>
                        <!-- WiFi signal icon -->
                        <g transform="translate(280, 40)">
                            <path d="M10 18 L10 14" stroke="#1b1b18" stroke-width="2" stroke-linecap="round"/>
                            <path d="M3 11 C6 7, 14 7, 17 11" stroke="#1b1b18" stroke-width="1.5" fill="none" stroke-linecap="round"/>
                            <path d="M0 7 C5 1, 15 1, 20 7" stroke="#1b1b18" stroke-width="1.5" fill="none" stroke-linecap="round"/>
                            <circle cx="10" cy="18" r="2" fill="#1b1b18" class="pulse"/>
                        </g>
                        <!-- Fan illustration -->
                        <g transform="translate(180, 210)" class="fan-spin">
                            <circle cx="0" cy="0" r="45" fill="none" stroke="#e3e3e0" stroke-width="1"/>
                            <circle cx="0" cy="0" r="6" fill="#1b1b18"/>
                            <path d="M0,-6 C-15,-25 -5,-45 0,-45 C5,-45 15,-25 0,-6" fill="#1b1b18" opacity="0.8"/>
                            <path d="M6,0 C25,-15 45,-5 45,0 C45,5 25,15 6,0" fill="#1b1b18" opacity="0.8"/>
                            <path d="M0,6 C15,25 5,45 0,45 C-5,45 -15,25 0,6" fill="#1b1b18" opacity="0.8"/>
                            <path d="M-6,0 C-25,15 -45,5 -45,0 C-45,-5 -25,-15 -6,0" fill="#1b1b18" opacity="0.8"/>
                        </g>
                        <!-- Connection line from chip to fan -->
                        <line x1="180" y1="110" x2="180" y2="160" stroke="#e3e3e0" stroke-width="1" stroke-dasharray="4 4"/>
                        <!-- Temperature card -->
                        <g transform="translate(50, 290)">
                            <rect width="110" height="55" rx="8" fill="none" stroke="#e3e3e0" stroke-width="1"/>
                            <text x="15" y="25" fill="#706f6c" font-size="10" font-family="'Instrument Sans', sans-serif">Temperature</text>
                            <text x="15" y="45" fill="#1b1b18" font-size="18" font-weight="600" font-family="'Instrument Sans', sans-serif">24.5&#176;C</text>
                        </g>
                        <!-- Humidity card -->
                        <g transform="translate(200, 290)">
                            <rect width="110" height="55" rx="8" fill="none" stroke="#e3e3e0" stroke-width="1"/>
                            <text x="15" y="25" fill="#706f6c" font-size="10" font-family="'Instrument Sans', sans-serif">Humidity</text>
                            <text x="15" y="45" fill="#1b1b18" font-size="18" font-weight="600" font-family="'Instrument Sans', sans-serif">62%</text>
                        </g>
                        <!-- Voice mic icon -->
                        <g transform="translate(55, 40)">
                            <rect x="5" y="0" width="10" height="16" rx="5" fill="none" stroke="#1b1b18" stroke-width="1.5"/>
                            <path d="M0 12 C0 20, 20 20, 20 12" fill="none" stroke="#1b1b18" stroke-width="1.5"/>
                            <line x1="10" y1="20" x2="10" y2="25" stroke="#1b1b18" stroke-width="1.5"/>
                            <line x1="5" y1="25" x2="15" y2="25" stroke="#1b1b18" stroke-width="1.5"/>
                        </g>
                        <!-- Status indicator -->
                        <circle cx="310" cy="365" r="4" fill="#22c55e" class="pulse"/>
                        <text x="295" y="370" fill="#706f6c" font-size="8" text-anchor="end" font-family="'Instrument Sans', sans-serif">Online</text>
                    </svg>
                </div>
            </div>
        </div>
    </body>
</html>