<?php
/**
 * Migration Script for Shared Hosting
 * 
 * This script runs database migrations when SSH access is not available.
 * 
 * SECURITY: Delete this file after running migrations!
 * 
 * Usage:
 * 1. Upload this file to your public directory
 * 2. Visit: https://aqua-falcon-493970.hostingersite.com/migrate.php
 * 3. Delete this file after migrations complete
 */

// Security check - remove this check after first run, or use a password
$MIGRATION_PASSWORD = 'CHANGE_THIS_PASSWORD_BEFORE_RUNNING'; // Change this!
if (!isset($_GET['password']) || $_GET['password'] !== $MIGRATION_PASSWORD) {
    die('Access denied. Please provide the correct password in the URL: ?password=YOUR_PASSWORD');
}

// Bootstrap Laravel
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Set time limit for long migrations
set_time_limit(300); // 5 minutes

?>
<!DOCTYPE html>
<html>
<head>
    <title>Database Migration</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
        .success { color: green; padding: 10px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 4px; margin: 10px 0; }
        .error { color: red; padding: 10px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 4px; margin: 10px 0; }
        .info { color: #0c5460; padding: 10px; background: #d1ecf1; border: 1px solid #bee5eb; border-radius: 4px; margin: 10px 0; }
        pre { background: #f4f4f4; padding: 10px; border-radius: 4px; overflow-x: auto; }
        .warning { color: #856404; padding: 10px; background: #fff3cd; border: 1px solid #ffeeba; border-radius: 4px; margin: 10px 0; }
    </style>
</head>
<body>
    <h1>Database Migration Script</h1>
    
    <?php
    try {
        echo '<div class="info">Starting migrations...</div>';
        
        // Run migrations
        $exitCode = Artisan::call('migrate', [
            '--force' => true,
        ]);
        
        $output = Artisan::output();
        
        if ($exitCode === 0) {
            echo '<div class="success">✅ Migrations completed successfully!</div>';
        } else {
            echo '<div class="error">❌ Migration failed with exit code: ' . $exitCode . '</div>';
        }
        
        echo '<h3>Migration Output:</h3>';
        echo '<pre>' . htmlspecialchars($output) . '</pre>';
        
        // Check all required tables
        $requiredTables = [
            'users',
            'devices',
            'tickets',
            'technicians',
            'jobs',
            'services',
            'quotes',
            'payments',
            'checklists',
            'settings',
            'notifications',
            'personal_access_tokens',
            'components',
            'component_brands',
            'component_categories',
            'component_usage_logs',
            'ratings',
            'activity_logs',
            'cache',
            'cache_locks',
            'sessions',
            'password_reset_tokens',
        ];
        
        echo '<h3>Table Verification:</h3>';
        echo '<div style="max-height: 400px; overflow-y: auto;">';
        $allTablesExist = true;
        foreach ($requiredTables as $table) {
            try {
                $exists = \Illuminate\Support\Facades\Schema::hasTable($table);
                if ($exists) {
                    echo '<div class="success">✅ ' . htmlspecialchars($table) . ' table exists</div>';
                } else {
                    echo '<div class="error">❌ ' . htmlspecialchars($table) . ' table missing</div>';
                    $allTablesExist = false;
                }
            } catch (\Exception $e) {
                echo '<div class="error">Error checking ' . htmlspecialchars($table) . ': ' . htmlspecialchars($e->getMessage()) . '</div>';
                $allTablesExist = false;
            }
        }
        echo '</div>';
        
        if ($allTablesExist) {
            echo '<div class="success"><strong>✅ All required tables exist!</strong></div>';
        } else {
            echo '<div class="error"><strong>❌ Some tables are missing. Please check the migration output above.</strong></div>';
        }
        
    } catch (\Exception $e) {
        echo '<div class="error">❌ Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
        echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
    }
    ?>
    
    <div class="warning">
        <strong>⚠️ SECURITY WARNING:</strong><br>
        Please delete this file (migrate.php) immediately after running migrations!
    </div>
    
    <h3>Next Steps:</h3>
    <ol>
        <li>Verify all migrations ran successfully</li>
        <li>Delete this file: <code>public/migrate.php</code></li>
        <li>Test your API endpoints</li>
    </ol>
</body>
</html>
