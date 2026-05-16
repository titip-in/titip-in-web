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
            // preloved
            ['name' => 'Buku & Alat Kuliah', 'icon' => '📚', 'type' => 'preloved'],
            ['name' => 'Laptop & Aksesoris', 'icon' => '💻', 'type' => 'preloved'],
            ['name' => 'Gadget & Elektronik', 'icon' => '📱', 'type' => 'preloved'],
            ['name' => 'Fashion & Outfit', 'icon' => '👕', 'type' => 'preloved'],
            ['name' => 'Sepatu & Sneakers', 'icon' => '👟', 'type' => 'preloved'],
            ['name' => 'Gaming & Hobi', 'icon' => '🎮', 'type' => 'preloved'],
            ['name' => 'Kos & Perlengkapan Kamar', 'icon' => '🪑', 'type' => 'preloved'],
            ['name' => 'Kendaraan & Otomotif', 'icon' => '🚲', 'type' => 'preloved'],
            ['name' => 'Peralatan Dapur & Makan', 'icon' => '🍳', 'type' => 'preloved'],
            ['name' => 'Musik & Kreatif', 'icon' => '🎸', 'type' => 'preloved'],
            ['name' => 'Kecantikan & Perawatan', 'icon' => '🧴', 'type' => 'preloved'],
            ['name' => 'Kebutuhan Bayi & Anak', 'icon' => '🍼', 'type' => 'preloved'],
            ['name' => 'Olahraga & Fitness', 'icon' => '🏋️', 'type' => 'preloved'],
            ['name' => 'Tiket Event & Konser', 'icon' => '🎟️', 'type' => 'preloved'],
            ['name' => 'Koleksi & Merchandise', 'icon' => '🧩', 'type' => 'preloved'],
            ['name' => 'Lainnya', 'icon' => '📦', 'type' => 'preloved'],

            // jastip
            ['name' => 'Makanan & Minuman', 'icon' => '🍔', 'type' => 'jastip'],
            ['name' => 'Supermarket & Titip Belanja', 'icon' => '🛒', 'type' => 'jastip'],
            ['name' => 'Cafe & Nongkrong', 'icon' => '☕', 'type' => 'jastip'],
            ['name' => 'Minuman Viral & Dessert', 'icon' => '🧋', 'type' => 'jastip'],
            ['name' => 'Mall & Retail', 'icon' => '🛍️', 'type' => 'jastip'],
            ['name' => 'Buku & Alat Tulis', 'icon' => '📚', 'type' => 'jastip'],
            ['name' => 'Apotek & Kesehatan', 'icon' => '💊', 'type' => 'jastip'],
            ['name' => 'Game Store & Hobi', 'icon' => '🎮', 'type' => 'jastip'],
            ['name' => 'Fashion & Thrifting', 'icon' => '👕', 'type' => 'jastip'],
            ['name' => 'Oleh-Oleh & Kado', 'icon' => '🎁', 'type' => 'jastip'],
            ['name' => 'Barang Luar Negeri', 'icon' => '✈️', 'type' => 'jastip'],
            ['name' => 'Transport & Tiket', 'icon' => '🚉', 'type' => 'jastip'],
            ['name' => 'Event & Konser', 'icon' => '🎟️', 'type' => 'jastip'],
            ['name' => 'Laundry & Kebutuhan Kos', 'icon' => '🧺', 'type' => 'jastip'],
            ['name' => 'Titip Area Kampus', 'icon' => '🏪', 'type' => 'jastip'],
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