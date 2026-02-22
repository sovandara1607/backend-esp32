<?php

/**
 * Vercel Serverless PHP entry point for Laravel.
 *
 * Vercel's filesystem is read-only except /tmp,
 * so we redirect Laravel's storage paths there.
 */

// Show errors during initial setup (remove after confirming it works)
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// Ensure writable directories exist in /tmp
$storageDirs = [
   '/tmp/storage/framework/views',
   '/tmp/storage/framework/cache/data',
   '/tmp/storage/framework/sessions',
   '/tmp/storage/logs',
   '/tmp/storage/app/public',
];

foreach ($storageDirs as $dir) {
   if (!is_dir($dir)) {
      mkdir($dir, 0755, true);
   }
}

// Copy compiled views from the app's storage to /tmp if they exist
$sourceViews = __DIR__ . '/../storage/framework/views';
if (is_dir($sourceViews)) {
   foreach (glob($sourceViews . '/*.php') as $view) {
      copy($view, '/tmp/storage/framework/views/' . basename($view));
   }
}

// Point Laravel to the writable /tmp storage
$_ENV['APP_STORAGE'] = '/tmp/storage';
putenv('APP_STORAGE=/tmp/storage');

// Override config to work on Vercel (stateless environment)
$_ENV['LOG_CHANNEL'] = $_ENV['LOG_CHANNEL'] ?? 'stderr';
$_ENV['SESSION_DRIVER'] = $_ENV['SESSION_DRIVER'] ?? 'cookie';
$_ENV['CACHE_STORE'] = $_ENV['CACHE_STORE'] ?? 'array';
$_ENV['VIEW_COMPILED_PATH'] = '/tmp/storage/framework/views';

putenv('LOG_CHANNEL=' . ($_ENV['LOG_CHANNEL'] ?? 'stderr'));
putenv('SESSION_DRIVER=' . ($_ENV['SESSION_DRIVER'] ?? 'cookie'));
putenv('CACHE_STORE=' . ($_ENV['CACHE_STORE'] ?? 'array'));
putenv('VIEW_COMPILED_PATH=/tmp/storage/framework/views');

// Forward the request to the Laravel application
require __DIR__ . '/../public/index.php';
