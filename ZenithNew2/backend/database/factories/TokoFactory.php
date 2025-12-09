<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class TokoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'toko_name' => $this->faker->company(),
            'deskripsi' => $this->faker->sentence(),
            'id_user' => \App\Models\User::factory(),
        ];
    }
}
