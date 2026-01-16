<?php
/**
 * Simple Device Data Seeder - No Password Protection
 * 
 * WARNING: This is a simple version without password protection.
 * Only use this if you can secure it via .htaccess or delete immediately after use.
 * 
 * Usage: Visit https://aqua-falcon-493970.hostingersite.com/seed-devices-simple.php
 * 
 * SECURITY: Delete this file immediately after running!
 */

// Bootstrap Laravel
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

set_time_limit(600);

header('Content-Type: text/plain');

try {
    echo "Starting device data import...\n\n";
    
    // First, check if required tables exist
    echo "=== Step 1: Checking Database Tables ===\n";
    $requiredTables = ['device_types', 'device_brands', 'device_models'];
    $missingTables = [];
    
    foreach ($requiredTables as $table) {
        if (Schema::hasTable($table)) {
            echo "✅ Table `{$table}` exists\n";
        } else {
            echo "❌ Table `{$table}` is MISSING!\n";
            $missingTables[] = $table;
        }
    }
    
    if (!empty($missingTables)) {
        echo "\n❌ ERROR: Required database tables are missing!\n";
        echo "\n⚠️  You must run migrations first!\n";
        echo "Visit: migrate.php\n";
        echo "Or run: php artisan migrate\n\n";
        echo "Required migrations:\n";
        echo "  - 2026_01_16_100000_create_device_types_table.php\n";
        echo "  - 2026_01_16_100100_create_device_brands_table.php\n";
        echo "  - 2026_01_16_100200_create_device_models_table.php\n";
        echo "  - 2026_01_16_100300_update_devices_table_add_foreign_keys.php\n";
        exit(1);
    }
    
    echo "✅ All required tables exist. Proceeding with data import...\n\n";
    
    // Check CSV files
    $csvPath = __DIR__ . '/../database/data/';
    $files = ['all_mobile_phone_brands.csv', 'laptops.csv', 'printers-scanner.csv'];
    
    foreach ($files as $file) {
        if (file_exists($csvPath . $file)) {
            echo "✅ Found: {$file}\n";
        } else {
            echo "❌ Missing: {$file}\n";
        }
    }
    echo "\n";
    
    Artisan::call('db:seed', ['--class' => 'DeviceDataSeeder', '--force' => true]);
    
    $output = Artisan::output();
    echo $output;
    
    // Check counts
    $deviceTypeCount = \App\Models\DeviceType::count();
    $deviceBrandCount = \App\Models\DeviceBrand::count();
    $deviceModelCount = \App\Models\DeviceModel::count();
    
    echo "\n\n=== Import Summary ===\n";
    echo "Device Types: {$deviceTypeCount}\n";
    echo "Device Brands: {$deviceBrandCount}\n";
    echo "Device Models: {$deviceModelCount}\n";
    
    if ($deviceTypeCount > 0 && $deviceBrandCount > 0) {
        echo "\n✅ SUCCESS: Device data imported!\n";
    } else {
        echo "\n❌ ERROR: No data was imported\n";
    }
    
    echo "\n\n⚠️  IMPORTANT: Delete this file (seed-devices-simple.php) now!\n";
    
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
