<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\AlertWebController;
use App\Http\Controllers\TemperatureControlController;
use Illuminate\Support\Facades\Route;

// ─── Public / Guest Routes ─────────────────────
Route::get('/', function () {
    return auth()->check() ? redirect('/dashboard') : view('welcome');
});

// ─── Auth Routes ────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// ─── Authenticated Routes ───────────────────────
Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Device Management (CRUD)
    Route::resource('devices', DeviceController::class);
    Route::post('/devices/{device}/command', [DeviceController::class, 'sendCommand'])->name('devices.command');

    // Alerts
    Route::get('/alerts', [AlertWebController::class, 'index'])->name('alerts.index');
    Route::get('/alerts/create', [AlertWebController::class, 'create'])->name('alerts.create');
    Route::post('/alerts', [AlertWebController::class, 'store'])->name('alerts.store');
    Route::patch('/alerts/{alert}/read', [AlertWebController::class, 'markRead'])->name('alerts.mark-read');
    Route::post('/alerts/read-all', [AlertWebController::class, 'markAllRead'])->name('alerts.mark-all-read');
    Route::delete('/alerts/{alert}', [AlertWebController::class, 'destroy'])->name('alerts.destroy');

    // Voice Control
    Route::get('/voice-control', function () {
        return view('voice-control');
    })->name('voice-control');

    // Temperature Control
    Route::get('/temperature-control', [TemperatureControlController::class, 'index'])
        ->name('temperature-control');
    Route::post('/temperature-control/profiles', [TemperatureControlController::class, 'storeProfile'])
        ->name('temperature-control.store-profile');
    Route::delete('/temperature-control/profiles/{profile}', [TemperatureControlController::class, 'destroyProfile'])
        ->name('temperature-control.destroy-profile');
    Route::post('/temperature-control/profiles/{profile}/rules', [TemperatureControlController::class, 'storeRule'])
        ->name('temperature-control.store-rule');
    Route::delete('/temperature-control/profiles/{profile}/rules/{rule}', [TemperatureControlController::class, 'destroyRule'])
        ->name('temperature-control.destroy-rule');
    Route::post('/temperature-control/profiles/{profile}/activate', [TemperatureControlController::class, 'activate'])
        ->name('temperature-control.activate');
    Route::post('/temperature-control/deactivate', [TemperatureControlController::class, 'deactivate'])
        ->name('temperature-control.deactivate');
});
