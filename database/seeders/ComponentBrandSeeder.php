<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ComponentBrand;

class ComponentBrandSeeder extends Seeder
{
    public function run(): void
    {
        $brands = [
            'HP', 'Canon', 'Epson', 'Brother', 'Lexmark', 'Xerox', 'Samsung', 'Dell',
            'Ricoh', 'Konica Minolta', 'Kyocera', 'Sharp', 'Panasonic', 'Toshiba',
            'Oki', 'Fuji Xerox', 'Generic', 'Compatible', 'OEM'
        ];

        foreach ($brands as $brand) {
            ComponentBrand::firstOrCreate(
                ['slug' => \Str::slug($brand)],
                [
                    'name' => $brand,
                    'is_active' => true,
                ]
            );
        }
    }
}
