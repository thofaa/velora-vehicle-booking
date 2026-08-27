<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PersetujuanResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'level_persetujuan' => $this->level_persetujuan,
            'status' => $this->status,
            'approved_at' => $this->approved_at,
            'catatan' => $this->catatan,
            'id_pihak_penyetuju' => $this->id_pihak_penyetuju,
            'pemesanan' => $this->whenLoaded('pemesanan', fn () => [
                'id' => $this->pemesanan->id,
                'tanggal_mulai' => $this->pemesanan->tanggal_mulai,
                'tanggal_selesai' => $this->pemesanan->tanggal_selesai,
                'status' => $this->pemesanan->status,
                'kendaraan' => $this->pemesanan->relationLoaded('kendaraan')
                    ? [
                        'nomor_polisi' => $this->pemesanan->kendaraan->nomor_polisi,
                        'merk' => $this->pemesanan->kendaraan->merk,
                        'tipe' => $this->pemesanan->kendaraan->tipe,
                    ]
                    : null,
                'driver' => $this->pemesanan->relationLoaded('driver')
                    ? [
                        'nama' => $this->pemesanan->driver->nama,
                        'nomor_telepon' => $this->pemesanan->driver->nomor_telepon,
                    ]
                    : null,
                'admin' => $this->pemesanan->relationLoaded('admin')
                    ? [
                        'id' => $this->pemesanan->admin->id,
                        'name' => $this->pemesanan->admin->name,
                    ]
                    : null,
            ]),
            'level_sebelumnya' => $this->when(
                $this->pemesanan->relationLoaded('persetujuan'),
                fn () => $this->pemesanan->persetujuan
                    ->filter(fn ($item) => $item->level_persetujuan < $this->level_persetujuan)
                    ->values()
                    ->map(fn ($item) => [
                        'level' => $item->level_persetujuan,
                        'status' => $item->status,
                    ])
            ),
        ];
    }
}
