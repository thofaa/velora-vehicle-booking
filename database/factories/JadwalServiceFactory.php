<?php

namespace Database\Factories;

use App\Models\JadwalService;
use App\Models\Kendaraan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<JadwalService>
 */
class JadwalServiceFactory extends Factory
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
            'tanggal_service' => fake()->dateTimeBetween('-1 month', '+1 month'),
            'jenis_service' => fake()->randomElement(['Ganti oli', 'Servis rutin', 'Rotasi ban', 'Ganti filter']),
            'status' => fake()->randomElement([JadwalService::STATUS_TERJADWAL, JadwalService::STATUS_SELESAI]),
        ];
    }
}
