<?php

namespace App\Policies;

use App\Models\Persetujuan;
use App\Models\User;

class PersetujuanPolicy
{
    /**
     * Hanya penyetuju yang dapat melihat daftar persetujuan.
     */
    public function viewAny(User $user): bool
    {
        return $user->role === 'penyetuju';
    }

    /**
     * Penyetuju hanya dapat memproses persetujuan yang menjadi tanggung jawabnya.
     */
    public function process(User $user, Persetujuan $persetujuan): bool
    {
        return $user->role === 'penyetuju'
            && $persetujuan->id_pihak_penyetuju === $user->getKey();
    }
}
