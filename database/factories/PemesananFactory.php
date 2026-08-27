<?php

namespace Database\Factories;

use App\Models\Driver;
use App\Models\Kendaraan;
use App\Models\Pemesanan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Pemesanan>
 */
class PemesananFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $tanggalMulai = fake()->dateTimeBetween('-1 month', '+1 month');

        return [
            'id_driver' => Driver::factory(),
            'id_kendaraan' => Kendaraan::factory(),
            'id_admin' => User::factory()->admin(),
            'tanggal_mulai' => $tanggalMulai,
            'tanggal_selesai' => (clone $tanggalMulai)->modify('+'.fake()->numberBetween(1, 7).' days'),
            'status' => 'menunggu_persetujuan',
        ];
    }
}
