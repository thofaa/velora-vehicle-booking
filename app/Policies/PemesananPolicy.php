<?php

namespace App\Policies;

use App\Models\Pemesanan;
use App\Models\User;

class PemesananPolicy
{
    /**
     * Hanya admin yang dapat melihat dan membuat pemesanan.
     */
    public function viewAny(User $user): bool
    {
        return $user->role === 'admin';
    }

    public function view(User $user, Pemesanan $pemesanan): bool
    {
        return $user->role === 'admin';
    }

    public function create(User $user): bool
    {
        return $user->role === 'admin';
    }
}
