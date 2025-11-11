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
                'name' => 'Raditya',
                'email' => 'raditya@gmail.com',
                'password' => Hash::make('12345678'),
                'role' => 'user',
            ]
        );

        User::factory()->create(
            [
                'name' => 'Hafid',
                'email' => 'hafid@gmail.com',
                'password' => Hash::make('12345678'),
                'role' => 'user',
            ]
        );

        User::factory()->create(
            [
                'name' => 'admin',
                'email' => 'admin@admin.com',
                'password' => Hash::make('12345678'),
                'role' => 'admin',
            ]
        );

        User::factory()->create(
            [
                'name' => 'penjual',
                'email' => 'penjual@penjual.com',
                'password' => Hash::make('12345678'),
                'role' => 'penjual',
            ]
        );

        for ($i = 0; $i < 5; $i++) {
            User::factory()->create([
                'name' => 'User ' . $i,
                'email' => $i . '@user.id',
            ]);
        }

        $daftarKategori = ['laptop', 'hp', 'komponen'];
        foreach ($daftarKategori as $kat) {
            Category::factory()->create([
                'nama_kategori' => $kat
            ]);
        }

        Product::factory(10)->create();
        $productIds = Product::orderBy('id_produk')->pluck('id_produk')->take(10)->toArray();

        foreach ($productIds as $id) {
            \App\Models\Variant::factory(2)->create(['id_produk' => $id]);
            \App\Models\CategoryDetail::factory(2)->create(['id_produk' => $id]);
        }
    }
}
