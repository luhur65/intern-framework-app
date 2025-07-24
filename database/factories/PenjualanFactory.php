<?php

namespace Database\Factories;

use App\Models\Pelanggan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Penjualan>
 */
class PenjualanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'no_bukti' => strtoupper(fake()->bothify('???##')),
            'tgl_bukti' => fake()->dateTimeBetween('2024-01-01', '2025-12-31')->format('Y-m-d'),
            'pelanggan_id' => Pelanggan::inRandomOrder()->first()->id ?? Pelanggan::factory(),
        ];
    }
}
