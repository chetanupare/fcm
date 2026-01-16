<?php
/**
 * Database Connection Test Script
 * Run this on Hostinger to test your MySQL connection
 * Usage: php test-db-connection.php
 */

// Get the base path (one level up from public)
$basePath = dirname(__DIR__);

require $basePath.'/vendor/autoload.php';

$app = require_once $basePath.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Set content type for browser display
header('Content-Type: text/plain; charset=utf-8');

echo "=== Database Connection Test ===\n\n";

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable($basePath);
$dotenv->load();

echo "Configuration from .env:\n";
echo "DB_CONNECTION: " . env('DB_CONNECTION', 'not set') . "\n";
echo "DB_HOST: " . env('DB_HOST', 'not set') . "\n";
echo "DB_PORT: " . env('DB_PORT', 'not set') . "\n";
echo "DB_DATABASE: " . env('DB_DATABASE', 'not set') . "\n";
echo "DB_USERNAME: " . env('DB_USERNAME', 'not set') . "\n";
echo "DB_PASSWORD: " . (env('DB_PASSWORD') ? '***SET***' : 'NOT SET') . "\n";
echo "\n";

// Test direct PDO connection
echo "Testing direct PDO connection...\n";
try {
    $host = env('DB_HOST', '127.0.0.1');
    $port = env('DB_PORT', '3306');
    $database = env('DB_DATABASE');
    $username = env('DB_USERNAME');
    $password = env('DB_PASSWORD');
    
    $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";
    
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    
    echo "✅ Direct PDO connection successful!\n";
    
    // Test query
    $stmt = $pdo->query("SELECT DATABASE() as current_db, USER() as current_user");
    $result = $stmt->fetch();
    echo "Current Database: " . $result['current_db'] . "\n";
    echo "Current User: " . $result['current_user'] . "\n";
    
} catch (PDOException $e) {
    echo "❌ Direct PDO connection failed:\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "\n";
    echo "Troubleshooting:\n";
    echo "1. Check if password in .env is correct\n";
    echo "2. Try using '127.0.0.1' instead of 'localhost' for DB_HOST\n";
    echo "3. Check if MySQL user has proper permissions\n";
    echo "4. Verify database exists: mysql -u {$username} -p -e 'SHOW DATABASES;'\n";
}

echo "\n";

// Test Laravel connection
echo "Testing Laravel database connection...\n";
try {
    DB::connection()->getPdo();
    echo "✅ Laravel connection successful!\n";
} catch (Exception $e) {
    echo "❌ Laravel connection failed:\n";
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";
echo "\n⚠️  SECURITY WARNING: Delete this file after testing!\n";