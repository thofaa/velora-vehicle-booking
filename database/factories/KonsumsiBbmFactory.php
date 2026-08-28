<?php

namespace Database\Factories;

use App\Models\Kendaraan;
use App\Models\KonsumsiBbm;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<KonsumsiBbm>
 */
class KonsumsiBbmFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_kendaraan' => Kendaraan::factory(),
            'tanggal' => fake()->dateTimeBetween('-1 year', 'now'),
            'jumlah_liter' => fake()->randomFloat(2, 10, 60),
            'id_pemesanan' => null,
        ];
    }
}
