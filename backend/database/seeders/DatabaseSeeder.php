<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create(
            [
                'name' => 'User',
                'email' => 'user@zenith.id',
                'password' => Hash::make('123'),
                'role' => 'user',
            ]
        );

        User::factory()->create(
            [
                'name' => 'Penjual',
                'email' => 'penjual@zenith.id',
                'password' => Hash::make('123'),
                'role' => 'penjual',
            ]
        );

        \App\Models\Toko::factory()->create([
            'toko_name' => 'Toko 1',
            'deskripsi' => 'Toko 1',
            'id_user' => 2
        ]);

        User::factory()->create(
            [
                'name' => 'admin',
                'email' => 'admin@zenith.id',
                'password' => Hash::make('123'),
                'role' => 'admin',
            ]
        );

        $this->call(CategorySeeder::class);

        // Fetch categories to use their IDs for product assignment
        $categories = Category::all();
        $categoryIds = $categories->pluck('id_kategori', 'nama_kategori')->toArray();

        // Create 10 random products
        Product::factory(10)->create(['id_toko' => 1]);

        // Create 8 specific products for 'laptop' category to ensure we have enough for testing
        $laptopProducts = Product::factory(8)->create([
            'id_toko' => 1,
            'nama_produk' => 'Laptop Gaming High End ' . rand(100, 999)
        ]);

        // Assign categories to random products
        $productIds = Product::orderBy('id_produk')->pluck('id_produk')->toArray();

        foreach ($productIds as $id) {
            \App\Models\Variant::factory(2)->create(['id_produk' => $id]);

            // Check if this product is one of our specific laptop products
            $isLaptop = $laptopProducts->contains('id_produk', $id);

            if ($isLaptop) {
                \App\Models\CategoryDetail::create([
                    'id_produk' => $id,
                    'id_kategori' => $categoryIds['Laptop'] ?? $categories->first()->id_kategori
                ]);
            } else {
                \App\Models\CategoryDetail::factory(1)->create(['id_produk' => $id]);
            }
        }
    }
}
