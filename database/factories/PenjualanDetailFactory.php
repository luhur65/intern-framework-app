<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PenjualanDetail>
 */
class PenjualanDetailFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama_barang' => strtoupper(fake()->word()),
            'qty' => $this->faker->numberBetween(1, 20),
            'harga' => $this->faker->numberBetween(1000, 200000),
        ];
    }
}
