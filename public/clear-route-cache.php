<?php
/**
 * Clear Laravel Route Cache
 * 
 * This script clears the route cache on shared hosting without SSH access.
 * Access this file via browser: https://your-domain.com/clear-route-cache.php
 */

// Bootstrap Laravel
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Artisan;

try {
    echo "<h1>Clearing Laravel Caches</h1>";
    echo "<hr>";
    
    // Clear route cache
    echo "<h2>1. Clearing Route Cache...</h2>";
    Artisan::call('route:clear');
    echo "<p style='color: green;'>✓ Route cache cleared</p>";
    
    // Clear config cache
    echo "<h2>2. Clearing Config Cache...</h2>";
    Artisan::call('config:clear');
    echo "<p style='color: green;'>✓ Config cache cleared</p>";
    
    // Clear application cache
    echo "<h2>3. Clearing Application Cache...</h2>";
    Artisan::call('cache:clear');
    echo "<p style='color: green;'>✓ Application cache cleared</p>";
    
    // Clear view cache
    echo "<h2>4. Clearing View Cache...</h2>";
    Artisan::call('view:clear');
    echo "<p style='color: green;'>✓ View cache cleared</p>";
    
    echo "<hr>";
    echo "<h2>Verifying Customer Routes:</h2>";
    Artisan::call('route:list', ['--path' => 'api/customer']);
    $output = Artisan::output();
    echo "<pre style='background: #f5f5f5; padding: 15px; border-radius: 5px;'>" . htmlspecialchars($output) . "</pre>";
    
    if (strpos($output, 'customer/tickets') !== false) {
        echo "<p style='color: green; font-weight: bold;'>✓ Route 'customer/tickets' found!</p>";
    } else {
        echo "<p style='color: red; font-weight: bold;'>✗ Route 'customer/tickets' NOT found in route list</p>";
    }
    
    echo "<hr>";
    echo "<h2>All Done!</h2>";
    echo "<p>You can now try accessing the API endpoint again.</p>";
    echo "<p><a href='/api/customer/tickets' target='_blank'>Test: /api/customer/tickets</a> (requires authentication)</p>";
    
} catch (\Exception $e) {
    echo "<h1 style='color: red;'>Error</h1>";
    echo "<p style='color: red;'>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<h3>Stack Trace:</h3>";
    echo "<pre style='background: #ffe6e6; padding: 15px; border-radius: 5px;'>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
