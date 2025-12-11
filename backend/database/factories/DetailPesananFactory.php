<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DetailPesanan>
 */
class DetailPesananFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'pesanan_id' => \App\Models\Pesanan::factory(),
            'id_varian' => \App\Models\Variant::factory(),
            'kuantitas' => $this->faker->numberBetween(1, 10),
            'harga' => $this->faker->numberBetween(10000, 100000),
        ];
    }
}
