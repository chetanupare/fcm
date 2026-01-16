<?php
/**
 * Phase 1 Migration Script - Triage, Skills, SLA, and Payment Features
 * 
 * This script runs the new Phase 1 migrations when SSH access is not available.
 * 
 * SECURITY: Delete this file after running migrations!
 * 
 * Usage:
 * 1. Upload this file to your public directory
 * 2. Visit: https://your-domain.com/migrate-phase1.php?password=YOUR_PASSWORD
 * 3. Delete this file after migrations complete
 */

// Security check - CHANGE THIS PASSWORD!
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
    <title>Phase 1 Database Migration</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 900px; margin: 50px auto; padding: 20px; background: #f5f5f5; }
        .container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 3px solid #3B82F6; padding-bottom: 10px; }
        h2 { color: #555; margin-top: 30px; }
        .success { color: #155724; padding: 12px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 4px; margin: 10px 0; }
        .error { color: #721c24; padding: 12px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 4px; margin: 10px 0; }
        .info { color: #0c5460; padding: 12px; background: #d1ecf1; border: 1px solid #bee5eb; border-radius: 4px; margin: 10px 0; }
        .warning { color: #856404; padding: 12px; background: #fff3cd; border: 1px solid #ffeeba; border-radius: 4px; margin: 10px 0; }
        pre { background: #f8f9fa; padding: 15px; border-radius: 4px; overflow-x: auto; border: 1px solid #dee2e6; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #dee2e6; }
        th { background-color: #3B82F6; color: white; font-weight: 600; }
        tr:hover { background-color: #f8f9fa; }
        .status-success { color: #28a745; font-weight: bold; }
        .status-error { color: #dc3545; font-weight: bold; }
        .status-skip { color: #6c757d; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 Phase 1 Database Migration</h1>
        <p>This script will run the following migrations:</p>
        <ul>
            <li>Technician Location History</li>
            <li>Job Distance Tracking</li>
            <li>Technician Skills Management</li>
            <li>SLA Configurations</li>
            <li>Ticket SLA Tracking</li>
            <li>Technician On-Call Status</li>
        </ul>
        
        <?php
        try {
            echo '<div class="info">⏳ Starting Phase 1 migrations...</div>';
            
            // List of Phase 1 migrations
            $phase1Migrations = [
                '2026_01_19_000001_create_technician_location_history_table',
                '2026_01_19_000002_add_distance_to_jobs_table',
                '2026_01_19_000003_create_technician_skills_table',
                '2026_01_19_000004_create_sla_configurations_table',
                '2026_01_19_000005_create_ticket_sla_tracking_table',
                '2026_01_19_000006_add_on_call_to_technicians_table',
            ];
            
            echo '<h2>Migration Progress</h2>';
            echo '<table>';
            echo '<tr><th>Migration</th><th>Status</th><th>Details</th></tr>';
            
            $successCount = 0;
            $errorCount = 0;
            $skipCount = 0;
            
            foreach ($phase1Migrations as $migration) {
                $migrationFile = __DIR__ . '/../database/migrations/' . $migration . '.php';
                
                if (!file_exists($migrationFile)) {
                    echo '<tr><td>' . htmlspecialchars($migration) . '</td>';
                    echo '<td class="status-error">❌ Error</td>';
                    echo '<td>Migration file not found</td></tr>';
                    $errorCount++;
                    continue;
                }
                
                try {
                    // Run individual migration
                    \Illuminate\Support\Facades\Artisan::call('migrate', [
                        '--path' => 'database/migrations/' . $migration . '.php',
                        '--force' => true,
                    ]);
                    
                    $output = \Illuminate\Support\Facades\Artisan::output();
                    
                    if (strpos($output, 'Migrated') !== false) {
                        echo '<tr><td>' . htmlspecialchars($migration) . '</td>';
                        echo '<td class="status-success">✅ Migrated</td>';
                        echo '<td>Successfully applied</td></tr>';
                        $successCount++;
                    } elseif (strpos($output, 'Nothing to migrate') !== false || strpos($output, 'already exists') !== false) {
                        echo '<tr><td>' . htmlspecialchars($migration) . '</td>';
                        echo '<td class="status-skip">⏭️ Skipped</td>';
                        echo '<td>Already migrated or table exists</td></tr>';
                        $skipCount++;
                    } else {
                        echo '<tr><td>' . htmlspecialchars($migration) . '</td>';
                        echo '<td class="status-success">✅ Done</td>';
                        echo '<td>' . htmlspecialchars(substr($output, 0, 100)) . '</td></tr>';
                        $successCount++;
                    }
                } catch (\Exception $e) {
                    echo '<tr><td>' . htmlspecialchars($migration) . '</td>';
                    echo '<td class="status-error">❌ Error</td>';
                    echo '<td>' . htmlspecialchars($e->getMessage()) . '</td></tr>';
                    $errorCount++;
                }
            }
            
            echo '</table>';
            
            // Summary
            echo '<h2>Summary</h2>';
            echo '<div class="info">';
            echo '<strong>Total Migrations:</strong> ' . count($phase1Migrations) . '<br>';
            echo '<span class="status-success">✅ Successful: ' . $successCount . '</span><br>';
            echo '<span class="status-skip">⏭️ Skipped: ' . $skipCount . '</span><br>';
            if ($errorCount > 0) {
                echo '<span class="status-error">❌ Errors: ' . $errorCount . '</span><br>';
            }
            echo '</div>';
            
            // Verify tables
            echo '<h2>Table Verification</h2>';
            $requiredTables = [
                'technician_location_history' => 'Technician Location History',
                'technician_skills' => 'Technician Skills',
                'sla_configurations' => 'SLA Configurations',
                'ticket_sla_tracking' => 'Ticket SLA Tracking',
            ];
            
            $allTablesExist = true;
            foreach ($requiredTables as $table => $description) {
                try {
                    $exists = \Illuminate\Support\Facades\Schema::hasTable($table);
                    if ($exists) {
                        echo '<div class="success">✅ ' . htmlspecialchars($description) . ' table exists</div>';
                        
                        // Check for SLA configurations data
                        if ($table === 'sla_configurations') {
                            $count = \Illuminate\Support\Facades\DB::table('sla_configurations')->count();
                            if ($count > 0) {
                                echo '<div class="info">   └─ ' . $count . ' SLA configurations loaded</div>';
                            } else {
                                echo '<div class="warning">   └─ ⚠️ No SLA configurations found (may need seeding)</div>';
                            }
                        }
                    } else {
                        echo '<div class="error">❌ ' . htmlspecialchars($description) . ' table missing</div>';
                        $allTablesExist = false;
                    }
                } catch (\Exception $e) {
                    echo '<div class="error">Error checking ' . htmlspecialchars($table) . ': ' . htmlspecialchars($e->getMessage()) . '</div>';
                    $allTablesExist = false;
                }
            }
            
            // Check columns added to existing tables
            echo '<h2>Column Verification</h2>';
            try {
                // Check service_jobs table
                if (\Illuminate\Support\Facades\Schema::hasColumn('service_jobs', 'distance_km')) {
                    echo '<div class="success">✅ service_jobs.distance_km column exists</div>';
                } else {
                    echo '<div class="error">❌ service_jobs.distance_km column missing</div>';
                    $allTablesExist = false;
                }
                
                if (\Illuminate\Support\Facades\Schema::hasColumn('service_jobs', 'estimated_duration_minutes')) {
                    echo '<div class="success">✅ service_jobs.estimated_duration_minutes column exists</div>';
                } else {
                    echo '<div class="error">❌ service_jobs.estimated_duration_minutes column missing</div>';
                    $allTablesExist = false;
                }
                
                // Check technicians table
                if (\Illuminate\Support\Facades\Schema::hasColumn('technicians', 'is_on_call')) {
                    echo '<div class="success">✅ technicians.is_on_call column exists</div>';
                } else {
                    echo '<div class="error">❌ technicians.is_on_call column missing</div>';
                    $allTablesExist = false;
                }
            } catch (\Exception $e) {
                echo '<div class="error">Error checking columns: ' . htmlspecialchars($e->getMessage()) . '</div>';
            }
            
            if ($allTablesExist && $errorCount === 0) {
                echo '<div class="success"><strong>🎉 All Phase 1 migrations completed successfully!</strong></div>';
            } else {
                echo '<div class="warning"><strong>⚠️ Some migrations may have issues. Please review the output above.</strong></div>';
            }
            
        } catch (\Exception $e) {
            echo '<div class="error">❌ Fatal Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
            echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
        }
        ?>
        
        <div class="warning" style="margin-top: 30px;">
            <strong>⚠️ SECURITY WARNING:</strong><br>
            Please delete this file (<code>public/migrate-phase1.php</code>) immediately after running migrations!
        </div>
        
        <h2>Next Steps:</h2>
        <ol>
            <li>Verify all migrations ran successfully (check the table verification above)</li>
            <li><strong>Delete this file</strong> for security: <code>public/migrate-phase1.php</code></li>
            <li>Test the new features:
                <ul>
                    <li>Access SLA Dashboard: <code>/admin/sla</code></li>
                    <li>Access Reconciliation: <code>/admin/reconciliation</code></li>
                    <li>Manage Technician Skills: <code>/admin/technicians/skills</code></li>
                </ul>
            </li>
        </ol>
    </div>
</body>
</html>
