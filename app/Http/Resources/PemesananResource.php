<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PemesananResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tanggal_mulai' => $this->tanggal_mulai,
            'tanggal_selesai' => $this->tanggal_selesai,
            'status' => $this->status,
            'kendaraan' => $this->whenLoaded('kendaraan', fn () => [
                'nomor_polisi' => $this->kendaraan->nomor_polisi,
                'merk' => $this->kendaraan->merk,
                'tipe' => $this->kendaraan->tipe,
            ]),
            'driver' => $this->whenLoaded('driver', fn () => [
                'nama' => $this->driver->nama,
                'nomor_telepon' => $this->driver->nomor_telepon,
            ]),
            'admin' => $this->whenLoaded('admin', fn () => [
                'id' => $this->admin->id,
                'name' => $this->admin->name,
            ]),
            'persetujuan' => PersetujuanResource::collection(
                $this->whenLoaded('persetujuan', fn () => $this->persetujuan)
            ),
            'created_at' => $this->created_at,
        ];
    }
}
