<?php

/**
 * Vercel Serverless PHP entry point for Laravel.
 *
 * Vercel's filesystem is read-only except /tmp,
 * so we redirect Laravel's storage paths there.
 */

// Ensure writable directories exist in /tmp
$storageDirs = [
   '/tmp/storage/framework/views',
   '/tmp/storage/framework/cache/data',
   '/tmp/storage/framework/sessions',
   '/tmp/storage/logs',
];

foreach ($storageDirs as $dir) {
   if (!is_dir($dir)) {
      mkdir($dir, 0755, true);
   }
}

// Point Laravel to the writable /tmp storage
$_ENV['APP_STORAGE'] = '/tmp/storage';
putenv('APP_STORAGE=/tmp/storage');

// Forward the request to the Laravel application
require __DIR__ . '/../public/index.php';
