<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use App\Models\Toko;
use App\Models\Variant;
use App\Models\CategoryDetail;
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

        // Create (or update) initial users idempotently so running the seeder multiple times
        // won't trigger unique constraint violations on the 'email' field.
        $user = User::updateOrCreate(
            ['email' => 'user@zenith.id'],
            [
                'name' => 'User',
                'password' => Hash::make('123'),
                'role' => 'user',
            ]
        );

        $penjual = User::updateOrCreate(
            ['email' => 'penjual@zenith.id'],
            [
                'name' => 'Penjual',
                'password' => Hash::make('123'),
                'role' => 'penjual',
            ]
        );

        // Create (or update) a toko owned by the 'penjual' user and keep the reference
        $toko = Toko::updateOrCreate(
            ['toko_name' => 'Toko 1'],
            [
                'deskripsi' => 'Toko 1',
                'id_user' => $penjual->id,
            ]
        );

        $admin = User::updateOrCreate(
            ['email' => 'admin@zenith.id'],
            [
                'name' => 'admin',
                'password' => Hash::make('123'),
                'role' => 'admin',
            ]
        );

        $this->call(CategorySeeder::class);

        // Fetch categories to use their IDs for product assignment
        $categories = Category::all();
        $categoryIds = $categories->pluck('id_kategori', 'nama_kategori')->toArray();

        // Create 10 random products assigned to our toko
        Product::factory(10)->create(['id_toko' => $toko->id]);

        // Create 8 specific products for 'laptop' category to ensure we have enough for testing
        $laptopProducts = Product::factory(8)->create([
            'id_toko' => $toko->id,
            'nama_produk' => 'Laptop Gaming High End ' . rand(100, 999)
        ]);

        // Assign categories to random products
        $productIds = Product::orderBy('id_produk')->pluck('id_produk')->toArray();

        foreach ($productIds as $id) {
            Variant::factory(2)->create(['id_produk' => $id]);

            // Check if this product is one of our specific laptop products
            $isLaptop = $laptopProducts->contains('id_produk', $id);

            if ($isLaptop) {
                CategoryDetail::create([
                    'id_produk' => $id,
                    'id_kategori' => $categoryIds['Laptop'] ?? $categories->first()->id_kategori
                ]);
            } else {
                CategoryDetail::factory(1)->create(['id_produk' => $id]);
            }
        }
    }
}
