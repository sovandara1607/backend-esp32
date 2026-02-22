# IoT Fan Monitoring & Control System 

A web-based dashboard and API backend for monitoring and controlling ESP32-connected fans and sensors. Built with Laravel 12, Tailwind CSS 4, and Chart.js.

## Features

- **Unified Dashboard** — A single centralized interface combining device monitoring, fan control, temperature automation, voice commands, and alerts.
- **ESP32 Fan Control** — Turn the fan on/off and adjust speed (0–255) in real time via REST API endpoints the ESP32 polls.
- **Sensor Data & Charts** — Ingests temperature and humidity readings from ESP32 sensors and displays them in interactive time-series charts (1h / 6h / 24h / 7d).
- **Temperature Profiles** — Create rule-based profiles (e.g. ≥35°C → 100% speed) that automatically adjust fan speed based on incoming sensor data.
- **Voice Control** — Browser-based speech recognition with wake-word detection ("Hey Fan") and hands-free mode. Also supports Google Assistant commands via Sinric Pro integration.
- **Device Management** — Register, view, and manage multiple ESP32 devices with online/offline status tracking.
- **Alerts** — Automated alerts with severity levels, read/unread tracking, and real-time dashboard display.
- **Authentication** — User registration, login, and API token auth via Laravel Sanctum.

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Framework | Laravel 12 (PHP 8.2+) |
| Frontend | Blade, Tailwind CSS 4, Vite 7 |
| Charts | Chart.js 4 |
| HTTP Client | Axios |
| Auth | Laravel Sanctum |
| Hardware | ESP32 (fan + DHT sensor) |
| Voice | Web Speech API, Sinric Pro |

## Requirements

- PHP 8.2+
- Composer
- Node.js & npm
- MySQL / SQLite / PostgreSQL
- ESP32 device (for hardware integration)

## Installation

```bash
# Clone the repository
git clone https://github.com/sovandara1607/backend-esp32.git
cd backend-esp32

# Install PHP dependencies
composer install

# Install frontend dependencies
npm install

# Copy environment file and generate app key
cp .env.example .env
php artisan key:generate

# Configure your database in .env, then run migrations
php artisan migrate

# Build frontend assets
npm run build

# Start the development server
php artisan serve
```

For local development with hot-reload:

```bash
# Run Vite dev server + Laravel in parallel
npm run dev        # Terminal 1
php artisan serve  # Terminal 2
```

## Environment Variables

Add these to your `.env` for Sinric Pro / Google Assistant integration:

```env
SINRIC_APP_KEY=your-app-key
SINRIC_APP_SECRET=your-app-secret
SINRIC_DEVICE_ID=your-device-id
```

## API Endpoints

### ESP32 Fan Control (public)

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/fan/status` | Get current fan state and speed |
| GET | `/api/fan/on` | Turn fan on |
| GET | `/api/fan/off` | Turn fan off |
| GET | `/api/fan/speed/{value}` | Set fan speed (0–255) |

### Sensor Data (public)

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/sensor-data` | Submit sensor reading from ESP32 |
| GET | `/api/devices/{id}/sensor-data/chart` | Get chart data (params: `sensor_type`, `hours`) |
| GET | `/api/devices/{id}/sensor-data/latest` | Get latest reading |

### Sinric Pro / Voice (public)

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/sinric/status` | Check Sinric Pro connection |
| POST | `/api/sinric/command` | Execute a voice command |
| POST | `/api/sinric/callback` | Sinric Pro webhook callback |

### Device Commands (ESP32 polling)

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/devices/{id}/commands/pending` | Get pending commands |
| POST | `/api/devices/{id}/commands/{cmdId}/ack` | Acknowledge command |

### Authenticated API (Sanctum token required)

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/register` | Register new user |
| POST | `/api/login` | Login and get token |
| POST | `/api/logout` | Logout |
| GET | `/api/devices` | List user devices |
| POST | `/api/devices` | Create device |

## Project Structure

```
app/
├── Http/Controllers/         # Web & API controllers
├── Models/                   # Eloquent models (Device, SensorData, Alert, etc.)
├── Providers/                # Service providers
database/
├── migrations/               # Database schema
├── seeders/                  # Seed data
resources/
├── views/                    # Blade templates (dashboard, auth, devices, alerts)
├── css/                      # Tailwind entry
├── js/                       # Axios bootstrap
routes/
├── web.php                   # Web routes (dashboard, devices, alerts, temp control)
├── api.php                   # API routes (ESP32, sensor data, Sinric Pro)
```

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
