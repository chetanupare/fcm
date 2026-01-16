<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Technician;
use App\Models\Service;
use App\Models\Checklist;
use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@repair.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'phone' => '+1234567890',
            ]
        );

        // Create sample technician
        $technicianUser = User::firstOrCreate(
            ['email' => 'tech@repair.com'],
            [
                'name' => 'John Technician',
                'password' => Hash::make('password'),
                'role' => 'technician',
                'phone' => '+1234567891',
            ]
        );

        Technician::firstOrCreate(
            ['user_id' => $technicianUser->id],
            [
                'status' => 'off_duty',
                'commission_rate' => 15.00,
            ]
        );

        // Create sample customer
        User::firstOrCreate(
            ['email' => 'customer@repair.com'],
            [
                'name' => 'Jane Customer',
                'password' => Hash::make('password'),
                'role' => 'customer',
                'phone' => '+1234567892',
            ]
        );

        // Create sample services
        $services = [
            ['name' => 'Motherboard Repair', 'device_type' => 'laptop', 'price' => 150.00, 'category' => 'repair'],
            ['name' => 'Screen Replacement', 'device_type' => 'phone', 'price' => 80.00, 'category' => 'repair'],
            ['name' => 'Diagnosis Fee', 'device_type' => 'universal', 'price' => 25.00, 'category' => 'diagnosis'],
            ['name' => 'Visit Fee', 'device_type' => 'universal', 'price' => 15.00, 'category' => 'visit_fee'],
            ['name' => 'Battery Replacement', 'device_type' => 'phone', 'price' => 50.00, 'category' => 'part'],
            ['name' => 'AC Refrigerant Refill', 'device_type' => 'ac', 'price' => 120.00, 'category' => 'repair'],
        ];

        foreach ($services as $service) {
            Service::firstOrCreate(
                ['name' => $service['name']],
                array_merge($service, ['is_active' => true])
            );
        }

        // Create sample checklists
        $checklists = [
            ['device_type' => 'laptop', 'name' => 'Check Fan', 'description' => 'Verify fan is working properly', 'is_mandatory' => true, 'order' => 1],
            ['device_type' => 'laptop', 'name' => 'Check Thermal Paste', 'description' => 'Apply thermal paste if needed', 'is_mandatory' => true, 'order' => 2],
            ['device_type' => 'phone', 'name' => 'Test Screen Touch', 'description' => 'Verify touch functionality', 'is_mandatory' => true, 'order' => 1],
            ['device_type' => 'phone', 'name' => 'Check Battery Health', 'description' => 'Verify battery capacity', 'is_mandatory' => true, 'order' => 2],
            ['device_type' => 'universal', 'name' => 'Clean Device', 'description' => 'Clean device exterior', 'is_mandatory' => false, 'order' => 99],
        ];

        foreach ($checklists as $checklist) {
            Checklist::firstOrCreate(
                [
                    'device_type' => $checklist['device_type'],
                    'name' => $checklist['name'],
                ],
                $checklist
            );
        }

        // Create default settings
        Setting::set('triage_timeout_minutes', 5, 'workflow');
        Setting::set('job_offer_timeout_minutes', 5, 'workflow');
        Setting::set('require_photos', false, 'workflow');
        Setting::set('tax_rate', 0, 'workflow');
        
        Setting::set('app_name', 'Repair Management System', 'white_label');
        Setting::set('primary_color', '#3B82F6', 'white_label');
        Setting::set('secondary_color', '#1E40AF', 'white_label');
        
        Setting::set('default_currency', 'USD', 'localization');
        Setting::set('supported_currencies', ['USD', 'EUR', 'INR'], 'localization');
        Setting::set('supported_languages', ['en', 'ar', 'he'], 'localization');

        // Call additional seeders
        $this->call([
            ComponentCategorySeeder::class,
            ComponentBrandSeeder::class,
            ComponentSeeder::class,
            DeviceDataSeeder::class, // Import device types, brands, and models from CSV
            DummyDataSeeder::class,
        ]);
    }
}
