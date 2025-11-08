<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Variant>
 */
class VariantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama_varian' => $this->faker->word(),
            'harga' => $this->faker->numberBetween(100000, 600000),
            'stok' => $this->faker->numberBetween(5, 20),
            'gambar_varian' => '../img_placeholder.jpg',
        ];
    }
}
