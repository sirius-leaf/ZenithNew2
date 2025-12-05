<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Pesanan>
 */
class PesananFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'toko_id' => \App\Models\Toko::factory(),
            'total_harga' => $this->faker->numberBetween(10000, 1000000),
            'status' => 'pending',
            'alamat_pengiriman' => $this->faker->address(),
            'payment_method' => 'cod',
        ];
    }
}
