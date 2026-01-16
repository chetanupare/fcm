<?php
/**
 * Simple Migration Script - No Password Protection
 * 
 * WARNING: This is a simple version without password protection.
 * Only use this if you can secure it via .htaccess or delete immediately after use.
 * 
 * Usage: Visit https://aqua-falcon-493970.hostingersite.com/migrate-simple.php
 * 
 * SECURITY: Delete this file immediately after running!
 */

// Bootstrap Laravel
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

set_time_limit(300);

header('Content-Type: text/plain');

try {
    echo "Starting database migrations...\n\n";
    
    Artisan::call('migrate', ['--force' => true]);
    
    $output = Artisan::output();
    echo $output;
    
    // Check table
    if (\Illuminate\Support\Facades\Schema::hasTable('personal_access_tokens')) {
        echo "\n✅ SUCCESS: personal_access_tokens table created!\n";
    } else {
        echo "\n❌ ERROR: personal_access_tokens table not found!\n";
    }
    
    echo "\n\n⚠️  IMPORTANT: Delete this file (migrate-simple.php) now!\n";
    
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
