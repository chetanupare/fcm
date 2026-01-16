<?php

use Dedoc\Scramble\Scramble;

return [
    /*
    |--------------------------------------------------------------------------
    | API Documentation Info
    |--------------------------------------------------------------------------
    */
    'info' => [
        'version' => env('API_VERSION', '1.0.0'),
        'title' => env('APP_NAME', 'Field Service Management API'),
        'description' => 'API documentation for Field Service Management System - Repair Shop Management',
    ],

    'servers' => [
        'API Server' => env('APP_URL', 'http://localhost'),
    ],

    'tags' => [
        [
            'name' => 'Admin',
            'description' => 'Administrator endpoints for managing the system',
        ],
        [
            'name' => 'Customer',
            'description' => 'Customer endpoints for bookings and tracking',
        ],
        [
            'name' => 'Technician',
            'description' => 'Technician endpoints for job management',
        ],
        [
            'name' => 'Misc',
            'description' => 'Miscellaneous endpoints (auth, webhooks, etc.)',
        ],
    ],

    'extensions' => [
        \Dedoc\Scramble\Extensions\OperationExtension::class => [
            \Dedoc\Scramble\Support\Generator\Extensions\OperationExtension::class,
        ],
    ],
];
