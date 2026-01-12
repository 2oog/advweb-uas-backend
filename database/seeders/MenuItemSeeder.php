<?php

namespace Database\Seeders;

use App\Models\MenuItem;
use Illuminate\Database\Seeder;

class MenuItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Foods
        MenuItem::create([
            'name' => 'Nasi Goreng Special',
            'price' => 25000,
            'image_asset' => 'images/nasi_goreng_sambal_matah.jpg',
        ]);
        MenuItem::create([
            'name' => 'Nasi Goreng Seafood',
            'price' => 28000,
            'image_asset' => 'images/Resep-Nasgor-Seafood.jpg',
        ]);
        MenuItem::create([
            'name' => 'Ayam Bakar',
            'price' => 30000,
            'image_asset' => 'images/nasi_ayam_bakar.jpg',
        ]);
        MenuItem::create([
            'name' => 'Sate Ayam (10x)',
            'price' => 35000,
            'image_asset' => 'images/sate-ayam.jpg',
        ]);

        // 2. Drinks
        MenuItem::create([
            'name' => 'Es Teh Manis',
            'price' => 5000,
            'image_asset' => 'images/es-teh.webp',
        ]);
        MenuItem::create([
            'name' => 'Es Jeruk',
            'price' => 8000,
            'image_asset' => 'images/es-jeruk.jpg',
        ]);
        MenuItem::create([
            'name' => 'Kopi Hitam',
            'price' => 10000,
            'image_asset' => 'images/kopi-hitam.jpg',
        ]);
        MenuItem::create([
            'name' => 'Mineral Water 600ml',
            'price' => 4000,
            'image_asset' => 'images/mineral-600ml.webp',
        ]);

        // 3. Condiments / Extras
        MenuItem::create([
            'name' => 'Sambal Terasi',
            'price' => 3000,
            'image_asset' => 'images/sambal-terasi.jpeg',
        ]);
        MenuItem::create([
            'name' => 'Nasi Putih',
            'price' => 5000,
            'image_asset' => 'images/nasi-putih.jpg',
        ]);
        MenuItem::create([
            'name' => 'Kerupuk Putih',
            'price' => 2000,
            'image_asset' => 'images/kerupuk.jpg',
        ]);
        MenuItem::create([
            'name' => 'Telur Dadar',
            'price' => 5000,
            'image_asset' => 'images/telur-dadar.jpg',
        ]);
    }
}
