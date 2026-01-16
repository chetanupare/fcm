<?php
/**
 * Combined Migration and Seeding Script
 * 
 * This script runs migrations first, then seeds device data.
 * 
 * SECURITY: Delete this file after running!
 * 
 * Usage:
 * 1. Edit the password below
 * 2. Visit: https://aqua-falcon-493970.hostingersite.com/migrate-and-seed.php?password=YOUR_PASSWORD
 * 3. Delete this file after completion
 */

// Security check - change this password!
$PASSWORD = 'CHANGE_THIS_PASSWORD_BEFORE_RUNNING';
if (!isset($_GET['password']) || $_GET['password'] !== $PASSWORD) {
    die('Access denied. Please provide the correct password in the URL: ?password=YOUR_PASSWORD');
}

// Bootstrap Laravel
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

set_time_limit(600); // 10 minutes

?>
<!DOCTYPE html>
<html>
<head>
    <title>Migration & Device Data Seeding</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 900px; margin: 50px auto; padding: 20px; }
        .success { color: green; padding: 10px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 4px; margin: 10px 0; }
        .error { color: red; padding: 10px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 4px; margin: 10px 0; }
        .info { color: #0c5460; padding: 10px; background: #d1ecf1; border: 1px solid #bee5eb; border-radius: 4px; margin: 10px 0; }
        .warning { color: #856404; padding: 10px; background: #fff3cd; border: 1px solid #ffeeba; border-radius: 4px; margin: 10px 0; }
        pre { background: #f4f4f4; padding: 10px; border-radius: 4px; overflow-x: auto; }
        h2 { border-bottom: 2px solid #ddd; padding-bottom: 10px; margin-top: 30px; }
    </style>
</head>
<body>
    <h1>Migration & Device Data Seeding</h1>
    
    <?php
    try {
        // STEP 1: Run Migrations
        echo '<h2>Step 1: Running Database Migrations</h2>';
        echo '<div class="info">Running migrations...</div>';
        
        $migrationExitCode = Artisan::call('migrate', ['--force' => true]);
        $migrationOutput = Artisan::output();
        
        if ($migrationExitCode === 0) {
            echo '<div class="success">✅ Migrations completed successfully!</div>';
        } else {
            echo '<div class="error">❌ Migration failed with exit code: ' . $migrationExitCode . '</div>';
        }
        
        echo '<h3>Migration Output:</h3>';
        echo '<pre>' . htmlspecialchars($migrationOutput) . '</pre>';
        
        // Verify required tables
        echo '<h3>Table Verification:</h3>';
        $requiredTables = [
            'device_types',
            'device_brands',
            'device_models',
            'devices',
            'tickets',
            'users',
        ];
        
        $allTablesExist = true;
        foreach ($requiredTables as $table) {
            if (Schema::hasTable($table)) {
                echo '<div class="success">✅ Table `' . htmlspecialchars($table) . '` exists</div>';
            } else {
                echo '<div class="error">❌ Table `' . htmlspecialchars($table) . '` is MISSING!</div>';
                $allTablesExist = false;
            }
        }
        
        if (!$allTablesExist) {
            echo '<div class="error">⚠️ Some required tables are missing. Please review the migration output above.</div>';
            echo '<div class="warning">Stopping here. Please fix migration issues before proceeding to seeding.</div>';
            exit;
        }
        
        // STEP 2: Seed Device Data
        echo '<h2>Step 2: Importing Device Data</h2>';
        
        // Check CSV files
        $csvPath = __DIR__ . '/../database/data/';
        $csvFiles = [
            'all_mobile_phone_brands.csv' => 'Mobile Phone Brands',
            'laptops.csv' => 'Laptops',
            'printers-scanner.csv' => 'Printers/Scanners',
        ];
        
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
        } else {
            // Run the seeder
            echo '<div class="info">Running device data seeder...</div>';
            $seederExitCode = Artisan::call('db:seed', [
                '--class' => 'DeviceDataSeeder',
                '--force' => true,
            ]);
            
            $seederOutput = Artisan::output();
            
            if ($seederExitCode === 0) {
                echo '<div class="success">✅ Device data imported successfully!</div>';
            } else {
                echo '<div class="error">❌ Seeding failed with exit code: ' . $seederExitCode . '</div>';
            }
            
            echo '<h3>Seeder Output:</h3>';
            echo '<pre>' . htmlspecialchars($seederOutput) . '</pre>';
            
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
                    echo '<div class="success">🎉 SUCCESS: All migrations and seeding completed!</div>';
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
        Please delete this file (migrate-and-seed.php) immediately after running!
    </div>
    
    <h3>Next Steps:</h3>
    <ol>
        <li>Verify the data was imported (check the summary above)</li>
        <li>Test the API endpoint: <code>GET /api/device-types</code></li>
        <li>Delete this file: <code>public/migrate-and-seed.php</code></li>
    </ol>
</body>
</html>
