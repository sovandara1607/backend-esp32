<?php

/**
 * Vercel Serverless PHP entry point for Laravel.
 *
 * Vercel's filesystem is read-only except /tmp,
 * so we redirect Laravel's storage paths there.
 */

// Show errors during initial setup
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// CRITICAL: Change working directory to the project root.
chdir(__DIR__ . '/..');

// Ensure writable directories exist in /tmp
$storageDirs = [
   '/tmp/storage/framework/views',
   '/tmp/storage/framework/cache/data',
   '/tmp/storage/framework/sessions',
   '/tmp/storage/logs',
   '/tmp/storage/app/public',
   '/tmp/bootstrap/cache',
];

foreach ($storageDirs as $dir) {
   if (!is_dir($dir)) {
      mkdir($dir, 0755, true);
   }
}

// Copy cached config/services/packages to writable location
$cacheFiles = ['services.php', 'packages.php'];
foreach ($cacheFiles as $file) {
   $source = __DIR__ . '/../bootstrap/cache/' . $file;
   $dest = '/tmp/bootstrap/cache/' . $file;
   if (file_exists($source) && !file_exists($dest)) {
      copy($source, $dest);
   }
}

// Copy compiled views from the app's storage to /tmp if they exist
$sourceViews = __DIR__ . '/../storage/framework/views';
if (is_dir($sourceViews)) {
   foreach (glob($sourceViews . '/*.php') as $view) {
      copy($view, '/tmp/storage/framework/views/' . basename($view));
   }
}

// Set environment variables BEFORE Laravel bootstraps
$envOverrides = [
   'APP_STORAGE' => '/tmp/storage',
   'APP_SERVICES_CACHE' => '/tmp/bootstrap/cache/services.php',
   'APP_PACKAGES_CACHE' => '/tmp/bootstrap/cache/packages.php',
   'APP_CONFIG_CACHE' => '/tmp/bootstrap/cache/config.php',
   'APP_ROUTES_CACHE' => '/tmp/bootstrap/cache/routes.php',
   'APP_EVENTS_CACHE' => '/tmp/bootstrap/cache/events.php',
   'LOG_CHANNEL' => 'stderr',
   'SESSION_DRIVER' => 'cookie',
   'CACHE_STORE' => 'array',
   'VIEW_COMPILED_PATH' => '/tmp/storage/framework/views',
];

foreach ($envOverrides as $key => $value) {
   // Don't override if already set via Vercel env vars (except paths)
   if (!isset($_ENV[$key]) || str_starts_with($key, 'APP_') || $key === 'VIEW_COMPILED_PATH') {
      $_ENV[$key] = $value;
      $_SERVER[$key] = $value;
      putenv("$key=$value");
   }
}

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
      'config_dir' => is_dir(__DIR__ . '/../config') ? 'EXISTS' : 'MISSING',
      'services_cache' => file_exists('/tmp/bootstrap/cache/services.php') ? 'EXISTS' : 'MISSING',
      'packages_cache' => file_exists('/tmp/bootstrap/cache/packages.php') ? 'EXISTS' : 'MISSING',
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
   // Walk up to find the original root cause
   $root = $e;
   while ($root->getPrevious()) {
      $root = $root->getPrevious();
   }

   header('Content-Type: application/json', true, 500);
   echo json_encode([
      'error' => $root->getMessage(),
      'class' => get_class($root),
      'file' => $root->getFile(),
      'line' => $root->getLine(),
      'trace' => array_slice(explode("\n", $root->getTraceAsString()), 0, 10),
      'outer_error' => ($root !== $e) ? $e->getMessage() : null,
   ], JSON_PRETTY_PRINT);
}
