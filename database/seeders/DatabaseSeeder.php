<?php

namespace Database\Seeders;

// use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Penjualan;
use App\Models\Pelanggan;
use App\Models\PenjualanDetail;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        Pelanggan::factory(10)->create(); // Create 10 Pelanggan records
        $penjualans = Penjualan::factory(50)->create(); // Create 50 Penjualan records
        // Create PenjualanDetail records for each Penjualan
        // Each Penjualan will have a random number of details between 2 and 10
        $penjualans->each(function ($penjualan) { 
            PenjualanDetail::factory(rand(2, 10))->create([
                'penjualan_id' => $penjualan->id,
            ]);
        });


    }
}
