<?php

namespace App\Services;

use App\Models\Pemesanan;

class PemesananStatusService
{
    /**
     * Evaluasi ulang status keseluruhan pemesanan berdasarkan status seluruh persetujuannya.
     *
     * Guard: jika pemesanan sudah `dibatalkan`, status tidak dievaluasi ulang.
     */
    public function refresh(Pemesanan $pemesanan): Pemesanan
    {
        if ($pemesanan->status === 'dibatalkan') {
            return $pemesanan;
        }

        $statuses = $pemesanan->persetujuan()->pluck('status');

        if ($statuses->contains('rejected')) {
            $pemesanan->status = 'ditolak';
        } elseif ($statuses->contains('pending')) {
            $pemesanan->status = 'menunggu_persetujuan';
        } elseif ($statuses->isNotEmpty()) {
            $pemesanan->status = 'disetujui';
        }

        $pemesanan->save();

        return $pemesanan;
    }
}
