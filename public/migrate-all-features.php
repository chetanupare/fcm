<?php
/**
 * Batch Migration Script for All Features
 * 
 * This script runs all new feature migrations on shared hosting without SSH access.
 * Access this file via browser: https://your-domain.com/migrate-all-features.php
 * 
 * WARNING: Remove this file after migrations are complete for security.
 */

// Bootstrap Laravel
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "<h1>Feature Migrations</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .success { color: green; }
    .error { color: red; }
    .info { color: blue; }
    pre { background: #f5f5f5; padding: 10px; border-radius: 5px; }
    table { border-collapse: collapse; width: 100%; margin: 20px 0; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #4CAF50; color: white; }
</style>";

try {
    $migrations = [
        // Phase 1: Core Features
        '2026_01_18_000001_create_amc_contracts_table',
        '2026_01_18_000002_create_expenses_table',
        '2026_01_18_000003_create_permissions_table', // Creates permissions, role_permissions, and user_permissions
        '2026_01_18_000004_create_invoices_table', // Renumbered from 000006
        '2026_01_18_000005_create_tasks_table', // Renumbered from 000007
        '2026_01_18_000006_create_pos_transactions_table', // Renumbered from 000008
        '2026_01_18_000007_create_delivery_otps_table', // Renumbered from 000009
        '2026_01_18_000008_create_digital_signatures_table', // Renumbered from 000010
        '2026_01_18_000009_create_leads_table', // Renumbered from 000011
        '2026_01_18_000010_create_outsource_vendors_table',
        '2026_01_18_000011_create_outsource_requests_table',
        
        // Phase 2: Enhanced Features
        '2026_01_18_000013_create_data_recovery_jobs_table',
        '2026_01_18_000015_create_suppliers_table', // Create suppliers BEFORE enhancing components
        '2026_01_18_000014_enhance_components_table_for_inventory',
        '2026_01_18_000023_add_supplier_foreign_key_to_components', // Add FK after suppliers exists
        '2026_01_18_000016_create_purchase_orders_table',
        '2026_01_18_000017_create_locations_table',
        '2026_01_18_000018_add_location_support_to_tables',
        '2026_01_18_000019_create_technician_performance_table',
        '2026_01_18_000020_create_customer_service_history_view',
        '2026_01_18_000021_create_integrations_table',
        '2026_01_18_000022_create_branding_settings_table',
        '2026_01_18_000024_update_customer_service_history_view_with_invoices', // Update view after invoices
        '2026_01_18_000025_add_invoice_foreign_key_to_digital_signatures', // Add FK after invoices
    ];

    echo "<h2>Running Migrations</h2>";
    echo "<table>";
    echo "<tr><th>Migration</th><th>Status</th><th>Message</th></tr>";

    $successCount = 0;
    $errorCount = 0;

    foreach ($migrations as $migration) {
        try {
            $migrationFile = __DIR__ . '/../database/migrations/' . $migration . '.php';
            
            if (!file_exists($migrationFile)) {
                echo "<tr><td>{$migration}</td><td class='error'>Error</td><td>File not found</td></tr>";
                $errorCount++;
                continue;
            }

            // Run migration using Artisan
            Artisan::call('migrate', [
                '--path' => 'database/migrations/' . $migration . '.php',
                '--force' => true
            ]);

            $output = Artisan::output();
            
            if (strpos($output, 'Migrated') !== false || strpos($output, 'Nothing to migrate') !== false) {
                echo "<tr><td>{$migration}</td><td class='success'>Success</td><td>Migration completed</td></tr>";
                $successCount++;
            } else {
                echo "<tr><td>{$migration}</td><td class='info'>Info</td><td>" . htmlspecialchars($output) . "</td></tr>";
                $successCount++;
            }
        } catch (\Exception $e) {
            echo "<tr><td>{$migration}</td><td class='error'>Error</td><td>" . htmlspecialchars($e->getMessage()) . "</td></tr>";
            $errorCount++;
        }
    }

    echo "</table>";

    echo "<h2>Summary</h2>";
    echo "<p class='success'>Successful: {$successCount}</p>";
    echo "<p class='error'>Errors: {$errorCount}</p>";

    // Verify tables
    echo "<h2>Verifying Tables</h2>";
    $expectedTables = [
        'amc_contracts',
        'expenses',
        'permissions',
        'role_permissions',
        'user_permissions',
        'invoices',
        'tasks',
        'pos_transactions',
        'delivery_otps',
        'digital_signatures',
        'leads',
        'outsource_vendors',
        'outsource_requests',
        'data_recovery_jobs',
        'suppliers',
        'purchase_orders',
        'locations',
        'technician_performance',
        'integrations',
        'branding_settings',
    ];

    echo "<table>";
    echo "<tr><th>Table</th><th>Status</th></tr>";

    foreach ($expectedTables as $table) {
        try {
            if (Schema::hasTable($table)) {
                $count = DB::table($table)->count();
                echo "<tr><td>{$table}</td><td class='success'>Exists ({$count} records)</td></tr>";
            } else {
                echo "<tr><td>{$table}</td><td class='error'>Missing</td></tr>";
            }
        } catch (\Exception $e) {
            echo "<tr><td>{$table}</td><td class='error'>Error: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
        }
    }

    echo "</table>";

    // Check enhanced components table
    echo "<h2>Checking Enhanced Components Table</h2>";
    if (Schema::hasTable('components')) {
        $columns = Schema::getColumnListing('components');
        $requiredColumns = ['barcode', 'supplier_id', 'reorder_level', 'reorder_quantity', 'location_id'];
        
        echo "<table>";
        echo "<tr><th>Column</th><th>Status</th></tr>";
        
        foreach ($requiredColumns as $column) {
            if (in_array($column, $columns)) {
                echo "<tr><td>{$column}</td><td class='success'>Exists</td></tr>";
            } else {
                echo "<tr><td>{$column}</td><td class='error'>Missing</td></tr>";
            }
        }
        echo "</table>";
    }

    // Check location support in other tables
    echo "<h2>Checking Location Support</h2>";
    $tablesWithLocation = ['tickets', 'service_jobs', 'technicians', 'components'];
    
    echo "<table>";
    echo "<tr><th>Table</th><th>location_id Column</th></tr>";
    
    foreach ($tablesWithLocation as $table) {
        if (Schema::hasTable($table)) {
            $columns = Schema::getColumnListing($table);
            if (in_array('location_id', $columns)) {
                echo "<tr><td>{$table}</td><td class='success'>Exists</td></tr>";
            } else {
                echo "<tr><td>{$table}</td><td class='error'>Missing</td></tr>";
            }
        }
    }
    echo "</table>";

    echo "<h2 class='success'>Migration Process Complete!</h2>";
    echo "<p><strong>Next Steps:</strong></p>";
    echo "<ul>";
    echo "<li>Seed initial data (permissions, locations, etc.)</li>";
    echo "<li>Test API endpoints</li>";
    echo "<li>Build frontend components</li>";
    echo "<li>Remove this migration script for security</li>";
    echo "</ul>";

} catch (\Exception $e) {
    echo "<h2 class='error'>Fatal Error</h2>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
