<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
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

        User::factory()->create([
            'name' => 'Raditya',
            'email' => 'raditya@gmail.com',
            'password' => Hash::make('12345678'),
            'role' => 'user',
        ]
    );

        User::factory()->create([
            'name' => 'Hafid',
            'email' => 'hafid@gmail.com',
            'password' => Hash::make('12345678'),
            'role' => 'user',
        ]
    );

        User::factory()->create([
            'name' => 'admin',
            'email' => 'admin@admin.com',
            'password' => Hash::make('12345678'),
            'role' => 'admin',
        ]
    );

        User::factory()->create([
            'name' => 'penjual',
            'email' => 'penjual@penjual.com',
            'password' => Hash::make('12345678'),
            'role' => 'penjual',
        ]
    );

        $daftarKategori = ['laptop', 'hp', 'komponen'];
        foreach ($daftarKategori as $kat) {
            Category::factory()->create([
                'nama_kategori' => $kat
            ]);
        }
    }
}
