<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Component;
use App\Models\ComponentCategory;
use App\Models\ComponentBrand;

class ComponentSeeder extends Seeder
{
    public function run(): void
    {
        $categories = ComponentCategory::all()->keyBy('name');
        $brands = ComponentBrand::all()->keyBy('name');

        $components = [
            // Consumables & Wear Parts
            [
                'name' => 'Black Toner Cartridge',
                'category' => 'Consumables & Wear Parts',
                'brand' => 'HP',
                'cost_price' => 25.00,
                'selling_price' => 45.00,
                'stock_quantity' => 50,
                'is_consumable' => true,
                'compatible_devices' => ['printer', 'multifunction'],
                'compatible_brands' => ['HP', 'Canon'],
            ],
            [
                'name' => 'Color Toner Cartridge Set',
                'category' => 'Consumables & Wear Parts',
                'brand' => 'HP',
                'cost_price' => 80.00,
                'selling_price' => 150.00,
                'stock_quantity' => 30,
                'is_consumable' => true,
            ],
            [
                'name' => 'Imaging Drum Unit',
                'category' => 'Consumables & Wear Parts',
                'brand' => 'HP',
                'cost_price' => 60.00,
                'selling_price' => 120.00,
                'stock_quantity' => 20,
                'is_consumable' => true,
            ],
            [
                'name' => 'Fuser Assembly',
                'category' => 'Consumables & Wear Parts',
                'brand' => 'HP',
                'cost_price' => 150.00,
                'selling_price' => 280.00,
                'stock_quantity' => 10,
                'is_consumable' => true,
            ],
            [
                'name' => 'Transfer Belt Unit',
                'category' => 'Consumables & Wear Parts',
                'brand' => 'Canon',
                'cost_price' => 120.00,
                'selling_price' => 220.00,
                'stock_quantity' => 15,
                'is_consumable' => true,
            ],
            [
                'name' => 'Ink Cartridge Set (CMYK)',
                'category' => 'Consumables & Wear Parts',
                'brand' => 'Epson',
                'cost_price' => 40.00,
                'selling_price' => 75.00,
                'stock_quantity' => 40,
                'is_consumable' => true,
            ],

            // Mechanical Assemblies
            [
                'name' => 'Paper Pickup Roller',
                'category' => 'Mechanical Assemblies',
                'brand' => 'Generic',
                'cost_price' => 8.00,
                'selling_price' => 18.00,
                'stock_quantity' => 100,
                'compatible_devices' => ['printer', 'scanner', 'multifunction'],
            ],
            [
                'name' => 'Separation Pad',
                'category' => 'Mechanical Assemblies',
                'brand' => 'Generic',
                'cost_price' => 5.00,
                'selling_price' => 12.00,
                'stock_quantity' => 150,
            ],
            [
                'name' => 'Feed Roller Assembly',
                'category' => 'Mechanical Assemblies',
                'brand' => 'HP',
                'cost_price' => 35.00,
                'selling_price' => 65.00,
                'stock_quantity' => 25,
            ],
            [
                'name' => 'Duplex Unit Assembly',
                'category' => 'Mechanical Assemblies',
                'brand' => 'Canon',
                'cost_price' => 180.00,
                'selling_price' => 320.00,
                'stock_quantity' => 8,
            ],
            [
                'name' => 'Paper Tray Assembly',
                'category' => 'Mechanical Assemblies',
                'brand' => 'HP',
                'cost_price' => 45.00,
                'selling_price' => 85.00,
                'stock_quantity' => 20,
            ],

            // Optical & Scanning
            [
                'name' => 'Scanner Glass / Platen',
                'category' => 'Optical & Scanning',
                'brand' => 'Generic',
                'cost_price' => 25.00,
                'selling_price' => 50.00,
                'stock_quantity' => 30,
            ],
            [
                'name' => 'Scanner Lamp / Backlight',
                'category' => 'Optical & Scanning',
                'brand' => 'Canon',
                'cost_price' => 55.00,
                'selling_price' => 110.00,
                'stock_quantity' => 15,
            ],
            [
                'name' => 'CCD Sensor Module',
                'category' => 'Optical & Scanning',
                'brand' => 'Epson',
                'cost_price' => 200.00,
                'selling_price' => 380.00,
                'stock_quantity' => 5,
            ],

            // Electronics & Boards
            [
                'name' => 'Formatter / Controller Board',
                'category' => 'Electronics & Boards',
                'brand' => 'HP',
                'cost_price' => 250.00,
                'selling_price' => 450.00,
                'stock_quantity' => 5,
            ],
            [
                'name' => 'Power Supply Unit',
                'category' => 'Electronics & Boards',
                'brand' => 'Generic',
                'cost_price' => 80.00,
                'selling_price' => 150.00,
                'stock_quantity' => 12,
            ],
            [
                'name' => 'Stepper Motor',
                'category' => 'Electronics & Boards',
                'brand' => 'Generic',
                'cost_price' => 45.00,
                'selling_price' => 85.00,
                'stock_quantity' => 20,
            ],
            [
                'name' => 'Paper Sensor',
                'category' => 'Electronics & Boards',
                'brand' => 'Generic',
                'cost_price' => 12.00,
                'selling_price' => 25.00,
                'stock_quantity' => 50,
            ],

            // Computer Components
            [
                'name' => 'Laptop RAM 8GB DDR4',
                'category' => 'Computer Components',
                'brand' => 'Generic',
                'cost_price' => 35.00,
                'selling_price' => 65.00,
                'stock_quantity' => 30,
                'compatible_devices' => ['laptop', 'desktop'],
            ],
            [
                'name' => 'SSD 256GB SATA',
                'category' => 'Computer Components',
                'brand' => 'Generic',
                'cost_price' => 45.00,
                'selling_price' => 85.00,
                'stock_quantity' => 25,
                'compatible_devices' => ['laptop', 'desktop'],
            ],
            [
                'name' => 'Laptop Battery',
                'category' => 'Computer Components',
                'brand' => 'Generic',
                'cost_price' => 60.00,
                'selling_price' => 120.00,
                'stock_quantity' => 20,
                'compatible_devices' => ['laptop'],
            ],
            [
                'name' => 'HDD 1TB 2.5"',
                'category' => 'Computer Components',
                'brand' => 'Generic',
                'cost_price' => 40.00,
                'selling_price' => 75.00,
                'stock_quantity' => 15,
                'compatible_devices' => ['laptop', 'desktop'],
            ],

            // Maintenance Kits
            [
                'name' => 'Maintenance Kit (Rollers & Belts)',
                'category' => 'Maintenance Kits',
                'brand' => 'HP',
                'cost_price' => 95.00,
                'selling_price' => 180.00,
                'stock_quantity' => 12,
            ],
            [
                'name' => 'Complete Maintenance Kit',
                'category' => 'Maintenance Kits',
                'brand' => 'Canon',
                'cost_price' => 150.00,
                'selling_price' => 280.00,
                'stock_quantity' => 8,
            ],
        ];

        foreach ($components as $index => $component) {
            $category = $categories[$component['category']] ?? $categories->first();
            $brand = $brands[$component['brand']] ?? $brands->first();
            $sku = 'COMP-' . str_pad($index + 1, 6, '0', STR_PAD_LEFT);

            Component::firstOrCreate(
                ['sku' => $sku],
                [
                    'name' => $component['name'],
                    'description' => $component['description'] ?? null,
                    'category_id' => $category->id,
                    'brand_id' => $brand->id,
                    'cost_price' => $component['cost_price'],
                    'selling_price' => $component['selling_price'],
                    'stock_quantity' => $component['stock_quantity'],
                    'min_stock_level' => 5,
                    'unit' => 'piece',
                    'compatible_devices' => $component['compatible_devices'] ?? null,
                    'compatible_brands' => $component['compatible_brands'] ?? null,
                    'part_number' => 'PN-' . strtoupper(substr($component['name'], 0, 3)) . '-' . ($index + 1),
                    'is_active' => true,
                    'is_consumable' => $component['is_consumable'] ?? false,
                ]
            );
        }
    }
}
