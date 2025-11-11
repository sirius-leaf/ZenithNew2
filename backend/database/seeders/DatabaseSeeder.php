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

        $daftarKategori = ['laptop', 'hp', 'komponen'];
        foreach ($daftarKategori as $kat) {
            Category::factory()->create([
                'nama_kategori' => $kat
            ]);
        }

        Product::factory(10)->create(['id_toko' => 1]);
        $productIds = Product::orderBy('id_produk')->pluck('id_produk')->take(10)->toArray();

        foreach ($productIds as $id) {
            \App\Models\Variant::factory(2)->create(['id_produk' => $id]);
            \App\Models\CategoryDetail::factory(2)->create(['id_produk' => $id]);
        }
    }
}
