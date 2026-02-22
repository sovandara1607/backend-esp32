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

// Debug endpoint — visit /api/debug to see what's going on
if (($_SERVER['REQUEST_URI'] ?? '') === '/api/debug') {
   header('Content-Type: application/json');
   echo json_encode([
      'php_version' => PHP_VERSION,
      'env_APP_KEY' => !empty(getenv('APP_KEY')) ? 'SET (' . strlen(getenv('APP_KEY')) . ' chars)' : 'NOT SET',
      'env_APP_ENV' => getenv('APP_ENV') ?: 'NOT SET',
      'env_APP_DEBUG' => getenv('APP_DEBUG') ?: 'NOT SET',
      'env_DB_HOST' => getenv('DB_HOST') ?: 'NOT SET',
      'env_DB_DATABASE' => getenv('DB_DATABASE') ?: 'NOT SET',
      'env_DB_CONNECTION' => getenv('DB_CONNECTION') ?: 'NOT SET',
      'vendor_autoload' => file_exists(__DIR__ . '/../vendor/autoload.php') ? 'EXISTS' : 'MISSING',
      'public_index' => file_exists(__DIR__ . '/../public/index.php') ? 'EXISTS' : 'MISSING',
      'bootstrap_app' => file_exists(__DIR__ . '/../bootstrap/app.php') ? 'EXISTS' : 'MISSING',
      'composer_json' => file_exists(__DIR__ . '/../composer.json') ? 'EXISTS' : 'MISSING',
      'tmp_storage' => is_dir('/tmp/storage') ? 'EXISTS' : 'MISSING',
      'cwd' => getcwd(),
      'script_dir' => __DIR__,
   ], JSON_PRETTY_PRINT);
   exit;
}

// Forward the request to the Laravel application — wrapped in try/catch
try {
   require __DIR__ . '/../public/index.php';
} catch (\Throwable $e) {
   header('Content-Type: application/json', true, 500);
   echo json_encode([
      'error' => $e->getMessage(),
      'file' => $e->getFile(),
      'line' => $e->getLine(),
      'trace' => array_slice(explode("\n", $e->getTraceAsString()), 0, 15),
   ], JSON_PRETTY_PRINT);
}
