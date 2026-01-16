<?php

namespace Database\Seeders;

use App\Models\DeviceType;
use App\Models\DeviceBrand;
use App\Models\DeviceModel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DeviceDataSeeder extends Seeder
{
    public function run(): void
    {
        // Create device types
        $deviceTypes = [
            ['name' => 'Laptop', 'slug' => 'laptop', 'icon' => 'laptop', 'sort_order' => 1],
            ['name' => 'Mobile Phone', 'slug' => 'phone', 'icon' => 'smartphone', 'sort_order' => 2],
            ['name' => 'Tablet', 'slug' => 'tablet', 'icon' => 'tablet', 'sort_order' => 3],
            ['name' => 'Desktop', 'slug' => 'desktop', 'icon' => 'monitor', 'sort_order' => 4],
            ['name' => 'Printer', 'slug' => 'printer', 'icon' => 'printer', 'sort_order' => 5],
            ['name' => 'Scanner', 'slug' => 'scanner', 'icon' => 'scanner', 'sort_order' => 6],
            ['name' => 'AC', 'slug' => 'ac', 'icon' => 'air-conditioner', 'sort_order' => 7],
            ['name' => 'Fridge', 'slug' => 'fridge', 'icon' => 'refrigerator', 'sort_order' => 8],
            ['name' => 'Other', 'slug' => 'other', 'icon' => 'device', 'sort_order' => 99],
        ];

        foreach ($deviceTypes as $type) {
            DeviceType::firstOrCreate(
                ['slug' => $type['slug']],
                $type
            );
        }

        $this->command->info('Device types created');

        // Import mobile phone brands
        $this->importMobilePhoneBrands();
        
        // Import laptop brands and models
        $this->importLaptops();
        
        // Import printer/scanner brands (from products)
        $this->importPrinterScannerBrands();

        $this->command->info('Device data imported successfully!');
    }

    private function importMobilePhoneBrands(): void
    {
        $phoneType = DeviceType::where('slug', 'phone')->first();
        if (!$phoneType) {
            return;
        }

        $csvPath = database_path('data/all_mobile_phone_brands.csv');
        if (!file_exists($csvPath)) {
            $this->command->warn("Mobile phone brands CSV not found: {$csvPath}");
            return;
        }

        $handle = fopen($csvPath, 'r');
        if (!$handle) {
            return;
        }

        // Skip header
        fgetcsv($handle);

        $count = 0;
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 3) continue;

            $brandName = trim($row[0]);
            $originCountry = trim($row[2] ?? '');

            if (empty($brandName)) continue;

            $brand = DeviceBrand::firstOrCreate(
                ['name' => $brandName],
                [
                    'slug' => Str::slug($brandName),
                    'origin_country' => $originCountry,
                    'is_active' => true,
                ]
            );

            $count++;
        }

        fclose($handle);
        $this->command->info("Imported {$count} mobile phone brands");
    }

    private function importLaptops(): void
    {
        $laptopType = DeviceType::where('slug', 'laptop')->first();
        if (!$laptopType) {
            return;
        }

        $csvPath = database_path('data/laptops.csv');
        if (!file_exists($csvPath)) {
            $this->command->warn("Laptops CSV not found: {$csvPath}");
            return;
        }

        $handle = fopen($csvPath, 'r');
        if (!$handle) {
            return;
        }

        // Skip header
        fgetcsv($handle);

        $brandCount = 0;
        $modelCount = 0;
        $brands = [];

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 3) continue;

            $brandName = trim($row[1] ?? '');
            $modelName = trim($row[2] ?? '');

            if (empty($brandName) || empty($modelName)) continue;

            // Create or get brand
            if (!isset($brands[$brandName])) {
                $brand = DeviceBrand::firstOrCreate(
                    ['name' => $brandName],
                    [
                        'slug' => Str::slug($brandName),
                        'is_active' => true,
                    ]
                );
                $brands[$brandName] = $brand->id;
                $brandCount++;
            } else {
                $brandId = $brands[$brandName];
            }

            // Create model
            DeviceModel::firstOrCreate(
                [
                    'device_type_id' => $laptopType->id,
                    'device_brand_id' => $brands[$brandName],
                    'name' => $modelName,
                ],
                [
                    'slug' => Str::slug($modelName),
                    'is_active' => true,
                ]
            );
            $modelCount++;
        }

        fclose($handle);
        $this->command->info("Imported {$brandCount} laptop brands and {$modelCount} laptop models");
    }

    private function importPrinterScannerBrands(): void
    {
        $printerType = DeviceType::where('slug', 'printer')->first();
        $scannerType = DeviceType::where('slug', 'scanner')->first();
        
        if (!$printerType || !$scannerType) {
            return;
        }

        $csvPath = database_path('data/printers-scanner.csv');
        if (!file_exists($csvPath)) {
            $this->command->warn("Printer/Scanner CSV not found: {$csvPath}");
            return;
        }

        $handle = fopen($csvPath, 'r');
        if (!$handle) {
            return;
        }

        // Skip header
        fgetcsv($handle);

        $brands = [];
        $count = 0;

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 2) continue;

            $name = trim($row[1] ?? '');
            if (empty($name)) continue;

            // Extract brand from product name (first word usually)
            $parts = explode(' ', $name);
            $brandName = trim($parts[0] ?? '');

            // Clean up common prefixes
            $brandName = preg_replace('/^[^a-zA-Z0-9]+/', '', $brandName);

            if (empty($brandName) || strlen($brandName) < 2) continue;

            // Determine if printer or scanner based on name
            $isPrinter = stripos($name, 'printer') !== false || stripos($name, 'print') !== false;
            $isScanner = stripos($name, 'scanner') !== false || stripos($name, 'scan') !== false;

            if (!$isPrinter && !$isScanner) {
                // Default to printer if unclear
                $isPrinter = true;
            }

            $deviceType = $isPrinter ? $printerType : $scannerType;

            if (!isset($brands[$brandName])) {
                $brand = DeviceBrand::firstOrCreate(
                    ['name' => $brandName],
                    [
                        'slug' => Str::slug($brandName),
                        'is_active' => true,
                    ]
                );
                $brands[$brandName] = $brand->id;
                $count++;
            }
        }

        fclose($handle);
        $this->command->info("Imported {$count} printer/scanner brands");
    }
}
