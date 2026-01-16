<?php
/**
 * Direct API test for recommendations
 * 
 * Usage: https://your-domain.com/test-recommendations-api.php?ticket_id=6
 * 
 * This bypasses authentication to test the recommendation logic directly
 */

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$ticketId = $_GET['ticket_id'] ?? 6;

echo "<h1>Recommendations API Test</h1>";
echo "<p>Testing recommendations for Ticket ID: <strong>{$ticketId}</strong></p>";
echo "<hr>";

try {
    $controller = new \App\Http\Controllers\Admin\TriageController();
    $reflection = new ReflectionClass($controller);
    $method = $reflection->getMethod('getRecommendedTechnicians');
    
    // Create a mock request
    $request = new \Illuminate\Http\Request();
    
    // Call the method directly
    $response = $method->invoke($controller, $ticketId);
    $data = json_decode($response->getContent(), true);
    
    echo "<h2>API Response</h2>";
    echo "<pre style='background: #f5f5f5; padding: 15px; border: 1px solid #ddd; overflow-x: auto;'>";
    echo json_encode($data, JSON_PRETTY_PRINT);
    echo "</pre>";
    
    echo "<hr>";
    echo "<h2>Debug Information</h2>";
    if (isset($data['debug'])) {
        echo "<ul>";
        foreach ($data['debug'] as $key => $value) {
            $displayValue = is_array($value) ? json_encode($value) : $value;
            $displayValue = $displayValue === null ? '<span style="color: red;">NULL</span>' : $displayValue;
            echo "<li><strong>{$key}:</strong> {$displayValue}</li>";
        }
        echo "</ul>";
    }
    
    echo "<hr>";
    echo "<h2>Recommendations</h2>";
    if (isset($data['recommendations']) && count($data['recommendations']) > 0) {
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr>";
        echo "<th>ID</th><th>Name</th><th>Skill Score</th><th>Distance (km)</th><th>Combined Score</th>";
        echo "</tr>";
        
        foreach ($data['recommendations'] as $rec) {
            echo "<tr>";
            echo "<td>{$rec['id']}</td>";
            echo "<td>{$rec['name']}</td>";
            echo "<td>{$rec['skill_match_score']}</td>";
            echo "<td>" . ($rec['distance_km'] ?? 'N/A') . "</td>";
            echo "<td>{$rec['combined_score']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: red;'><strong>No recommendations found!</strong></p>";
        echo "<h3>Possible Issues:</h3>";
        echo "<ul>";
        
        if (isset($data['debug']['device_type_id']) && $data['debug']['device_type_id'] === null) {
            echo "<li style='color: red;'><strong>Device missing device_type_id!</strong> Run update-device-types.php first.</li>";
        }
        
        if (isset($data['debug']['technicians_checked']) && $data['debug']['technicians_checked'] === 0) {
            echo "<li style='color: red;'><strong>No technicians found with status='on_duty' and active_jobs_count=0</strong></li>";
        }
        
        if (isset($data['debug']['technicians_with_skills']) && $data['debug']['technicians_with_skills'] === 0) {
            echo "<li style='color: red;'><strong>No technicians have matching skills for this device type</strong></li>";
        }
        
        echo "</ul>";
    }
    
    echo "<hr>";
    echo "<h2>Raw API Endpoint</h2>";
    echo "<p>Test the actual authenticated endpoint:</p>";
    echo "<p><a href='/admin/triage/{$ticketId}/recommendations' target='_blank'>/admin/triage/{$ticketId}/recommendations</a></p>";
    echo "<p><em>Note: Requires authentication. Check browser console for errors.</em></p>";
    
} catch (\Exception $e) {
    echo "<p style='color: red;'><strong>ERROR:</strong> " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
