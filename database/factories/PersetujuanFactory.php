<?php

namespace Database\Factories;

use App\Models\Pemesanan;
use App\Models\Persetujuan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Persetujuan>
 */
class PersetujuanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_pemesanan' => Pemesanan::factory(),
            'level_persetujuan' => 1,
            'id_pihak_penyetuju' => User::factory()->penyetuju(),
            'status' => 'pending',
            'approved_at' => null,
            'catatan' => null,
        ];
    }
}
