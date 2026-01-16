<?php
/**
 * Device Data Seeder Script for Shared Hosting
 * 
 * This script imports device types, brands, and models from CSV files.
 * 
 * SECURITY: Delete this file after running!
 * 
 * Usage:
 * 1. Upload this file to your public directory
 * 2. Visit: https://aqua-falcon-493970.hostingersite.com/seed-devices.php
 * 3. Delete this file after seeding completes
 */

// Bootstrap Laravel
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Set time limit for long operations
set_time_limit(600); // 10 minutes

?>
<!DOCTYPE html>
<html>
<head>
    <title>Device Data Seeder</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
        .success { color: green; padding: 10px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 4px; margin: 10px 0; }
        .error { color: red; padding: 10px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 4px; margin: 10px 0; }
        .info { color: #0c5460; padding: 10px; background: #d1ecf1; border: 1px solid #bee5eb; border-radius: 4px; margin: 10px 0; }
        .warning { color: #856404; padding: 10px; background: #fff3cd; border: 1px solid #ffeeba; border-radius: 4px; margin: 10px 0; }
        pre { background: #f4f4f4; padding: 10px; border-radius: 4px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>Device Data Seeder</h1>
    
    <?php
    try {
        echo '<div class="info">Starting device data import...</div>';
        
        // First, check if required tables exist
        echo '<h3>Step 1: Checking Database Tables</h3>';
        $requiredTables = ['device_types', 'device_brands', 'device_models'];
        $missingTables = [];
        
        foreach ($requiredTables as $table) {
            if (Schema::hasTable($table)) {
                echo '<div class="success">✅ Table `' . htmlspecialchars($table) . '` exists</div>';
            } else {
                echo '<div class="error">❌ Table `' . htmlspecialchars($table) . '` is MISSING!</div>';
                $missingTables[] = $table;
            }
        }
        
        if (!empty($missingTables)) {
            echo '<div class="error"><strong>❌ ERROR:</strong> Required database tables are missing!</div>';
            echo '<div class="warning">';
            echo '<strong>You must run migrations first!</strong><br>';
            echo 'Please visit: <a href="migrate.php" target="_blank">migrate.php</a><br>';
            echo 'Or run: <code>php artisan migrate</code> if you have terminal access.<br>';
            echo '</div>';
            echo '<div class="info">';
            echo '<strong>Required migrations:</strong><ul>';
            echo '<li>2026_01_16_100000_create_device_types_table.php</li>';
            echo '<li>2026_01_16_100100_create_device_brands_table.php</li>';
            echo '<li>2026_01_16_100200_create_device_models_table.php</li>';
            echo '<li>2026_01_16_100300_update_devices_table_add_foreign_keys.php</li>';
            echo '</ul></div>';
            exit;
        }
        
        echo '<div class="success">✅ All required tables exist. Proceeding with data import...</div>';
        echo '<br>';
        
        // Check if CSV files exist
        $csvFiles = [
            'all_mobile_phone_brands.csv' => 'Mobile Phone Brands',
            'laptops.csv' => 'Laptops',
            'printers-scanner.csv' => 'Printers/Scanners',
        ];
        
        $csvPath = __DIR__ . '/../database/data/';
        $missingFiles = [];
        
        foreach ($csvFiles as $file => $name) {
            if (!file_exists($csvPath . $file)) {
                $missingFiles[] = $file;
                echo '<div class="error">❌ Missing CSV file: ' . htmlspecialchars($file) . '</div>';
            } else {
                echo '<div class="success">✅ Found: ' . htmlspecialchars($file) . '</div>';
            }
        }
        
        if (!empty($missingFiles)) {
            echo '<div class="error"><strong>Error:</strong> Some CSV files are missing. Please ensure all CSV files are in database/data/ directory.</div>';
            echo '<div class="info">Required files:<ul>';
            foreach ($csvFiles as $file => $name) {
                echo '<li>' . htmlspecialchars($file) . ' - ' . htmlspecialchars($name) . '</li>';
            }
            echo '</ul></div>';
        } else {
            // Run the seeder
            $exitCode = Artisan::call('db:seed', [
                '--class' => 'DeviceDataSeeder',
                '--force' => true,
            ]);
            
            $output = Artisan::output();
            
            if ($exitCode === 0) {
                echo '<div class="success">✅ Device data imported successfully!</div>';
            } else {
                echo '<div class="error">❌ Seeding failed with exit code: ' . $exitCode . '</div>';
            }
            
            echo '<h3>Seeder Output:</h3>';
            echo '<pre>' . htmlspecialchars($output) . '</pre>';
            
            // Check imported data
            try {
                $deviceTypeCount = \App\Models\DeviceType::count();
                $deviceBrandCount = \App\Models\DeviceBrand::count();
                $deviceModelCount = \App\Models\DeviceModel::count();
                
                echo '<h3>Imported Data Summary:</h3>';
                echo '<div class="info">';
                echo '<strong>Device Types:</strong> ' . $deviceTypeCount . '<br>';
                echo '<strong>Device Brands:</strong> ' . $deviceBrandCount . '<br>';
                echo '<strong>Device Models:</strong> ' . $deviceModelCount . '<br>';
                echo '</div>';
                
                if ($deviceTypeCount > 0 && $deviceBrandCount > 0) {
                    echo '<div class="success">✅ Device data successfully imported!</div>';
                } else {
                    echo '<div class="error">❌ No data was imported. Check the seeder output above.</div>';
                }
            } catch (\Exception $e) {
                echo '<div class="error">Error checking imported data: ' . htmlspecialchars($e->getMessage()) . '</div>';
            }
        }
        
    } catch (\Exception $e) {
        echo '<div class="error">❌ Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
        echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
    }
    ?>
    
    <div class="warning">
        <strong>⚠️ SECURITY WARNING:</strong><br>
        Please delete this file (seed-devices.php) immediately after running!
    </div>
    
    <h3>Next Steps:</h3>
    <ol>
        <li>Verify the data was imported (check the summary above)</li>
        <li>Test the API endpoint: <code>GET /api/device-types</code></li>
        <li>Delete this file: <code>public/seed-devices.php</code></li>
    </ol>
</body>
</html>
