<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Disable foreign key checks to allow truncation
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Category::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $categories = [
            'Laptop',
            'Smartphone',
            'Processor',
            'Motherboard',
            'VGA Card',
            'RAM',
            'Storage',
            'Power Supply',
            'Casing PC',
            'Monitor',
            'Keyboard',
            'Mouse',
            'Headset & Audio',
            'Gaming Gear',
            'Networking',
            'Aksesoris & Kabel',
        ];

        foreach ($categories as $category) {
            Category::create(['nama_kategori' => $category]);
        }
    }
}
