<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Elektronik & Gadget', 'icon' => '📱', 'type' => 'preloved'],
            ['name' => 'Fashion & Pakaian', 'icon' => '👕', 'type' => 'preloved'],
            ['name' => 'Otomotif', 'icon' => '🚗', 'type' => 'preloved'],
            ['name' => 'Buku & Alat Tulis', 'icon' => '📚', 'type' => 'preloved'],
            ['name' => 'Makanan & Minuman (F&B)', 'icon' => '🍔', 'type' => 'jastip'],
            ['name' => 'Tiket Event & Konser', 'icon' => '🎟️', 'type' => 'jastip'],
            ['name' => 'Barang Luar Negeri', 'icon' => '✈️', 'type' => 'jastip'],
            ['name' => 'Lainnya', 'icon' => '📦', 'type' => 'jastip'],
        ];

        foreach ($categories as $category) {
            DB::table('categories')->insert(array_merge($category, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}