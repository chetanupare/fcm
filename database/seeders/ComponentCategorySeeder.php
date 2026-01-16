<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ComponentCategory;

class ComponentCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Consumables & Wear Parts', 'icon' => '🖨️', 'description' => 'Toner, ink, drums, fusers'],
            ['name' => 'Mechanical Assemblies', 'icon' => '⚙️', 'description' => 'Rollers, gears, belts, trays'],
            ['name' => 'Optical & Scanning', 'icon' => '📷', 'description' => 'Scanner glass, sensors, lamps'],
            ['name' => 'Electronics & Boards', 'icon' => '🔌', 'description' => 'Controller boards, power supplies'],
            ['name' => 'Case & Interface', 'icon' => '🖥️', 'description' => 'Covers, panels, displays'],
            ['name' => 'Maintenance Kits', 'icon' => '🧰', 'description' => 'Complete maintenance packages'],
            ['name' => 'Computer Components', 'icon' => '💻', 'description' => 'RAM, HDD, SSD, batteries'],
            ['name' => 'Cables & Connectors', 'icon' => '🔌', 'description' => 'Power cables, data cables'],
        ];

        foreach ($categories as $index => $category) {
            ComponentCategory::firstOrCreate(
                ['slug' => \Str::slug($category['name'])],
                [
                    'name' => $category['name'],
                    'description' => $category['description'],
                    'icon' => $category['icon'],
                    'is_active' => true,
                    'sort_order' => $index + 1,
                ]
            );
        }
    }
}
