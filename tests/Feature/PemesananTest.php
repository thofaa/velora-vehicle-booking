<?php

use App\Models\Driver;
use App\Models\Kendaraan;
use App\Models\Pemesanan;
use App\Models\User;

function pemesananPayload(array $overrides = []): array
{
    return array_merge([
        'tanggal_mulai' => now()->addDays(1)->format('Y-m-d H:i'),
        'tanggal_selesai' => now()->addDays(2)->format('Y-m-d H:i'),
        'id_kendaraan' => Kendaraan::factory()->create(['banyak_level_persetujuan' => 2])->id,
        'id_driver' => Driver::factory()->create()->id,
        'penyetuju' => [
            User::factory()->penyetuju()->create()->id,
            User::factory()->penyetuju()->create()->id,
        ],
    ], $overrides);
}

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->actingAs($this->admin);
});

test('penyetuju cannot access pemesanan creation', function () {
    $penyetuju = User::factory()->penyetuju()->create();
    $this->actingAs($penyetuju);

    $this->get(route('pemesanan.create'))->assertForbidden();
    $this->post(route('pemesanan.store'), pemesananPayload())->assertForbidden();
});

test('admin can create pemesanan and pending persetujuan records', function () {
    $payload = pemesananPayload();

    $this->post(route('pemesanan.store'), $payload)
        ->assertRedirect(route('pemesanan.index'));

    $pemesanan = Pemesanan::where('id_kendaraan', $payload['id_kendaraan'])->first();

    expect($pemesanan)->not->toBeNull()
        ->and($pemesanan->status)->toBe('menunggu_persetujuan')
        ->and($pemesanan->id_admin)->toBe($this->admin->id);

    expect($pemesanan->persetujuan()->count())->toBe(2)
        ->and($pemesanan->persetujuan()->pluck('status')->unique()->all())->toBe(['pending'])
        ->and($pemesanan->persetujuan()->pluck('level_persetujuan')->all())->toBe([1, 2]);
});

test('tanggal selesai harus lebih besar dari tanggal mulai', function () {
    $payload = pemesananPayload([
        'tanggal_mulai' => now()->addDays(2)->format('Y-m-d H:i'),
        'tanggal_selesai' => now()->addDays(1)->format('Y-m-d H:i'),
    ]);

    $this->post(route('pemesanan.store'), $payload)
        ->assertSessionHasErrors('tanggal_selesai');
});

test('kendaraan non-aktif tidak dapat dipilih', function () {
    $payload = pemesananPayload();
    $payload['id_kendaraan'] = Kendaraan::factory()->create(['status' => 'maintenance'])->id;

    $this->post(route('pemesanan.store'), $payload)
        ->assertSessionHasErrors('id_kendaraan');
});

test('driver non-aktif tidak dapat dipilih', function () {
    $payload = pemesananPayload();
    $payload['id_driver'] = Driver::factory()->create(['status' => 'cuti'])->id;

    $this->post(route('pemesanan.store'), $payload)
        ->assertSessionHasErrors('id_driver');
});

test('kendaraan yang overlap jadwal aktif ditolak', function () {
    $kendaraan = Kendaraan::factory()->create();
    $driver = Driver::factory()->create();
    $penyetuju = User::factory()->penyetuju()->create();

    $existing = Pemesanan::factory()->create([
        'id_kendaraan' => $kendaraan->id,
        'id_driver' => Driver::factory()->create()->id,
        'tanggal_mulai' => now()->addDays(1),
        'tanggal_selesai' => now()->addDays(3),
        'status' => 'disetujui',
    ]);

    $payload = pemesananPayload([
        'id_kendaraan' => $kendaraan->id,
        'id_driver' => $driver->id,
        'penyetuju' => [$penyetuju->id, User::factory()->penyetuju()->create()->id],
        'tanggal_mulai' => now()->addDays(2),
        'tanggal_selesai' => now()->addDays(4),
    ]);

    $this->post(route('pemesanan.store'), $payload)
        ->assertSessionHasErrors('id_kendaraan');
});

test('driver yang overlap jadwal aktif ditolak', function () {
    $driver = Driver::factory()->create();
    $penyetuju = User::factory()->penyetuju()->create();

    $existing = Pemesanan::factory()->create([
        'id_driver' => $driver->id,
        'id_kendaraan' => Kendaraan::factory()->create()->id,
        'tanggal_mulai' => now()->addDays(1),
        'tanggal_selesai' => now()->addDays(3),
        'status' => 'disetujui',
    ]);

    $payload = pemesananPayload([
        'id_driver' => $driver->id,
        'penyetuju' => [$penyetuju->id, User::factory()->penyetuju()->create()->id],
        'tanggal_mulai' => now()->addDays(2),
        'tanggal_selesai' => now()->addDays(4),
    ]);

    $this->post(route('pemesanan.store'), $payload)
        ->assertSessionHasErrors('id_driver');
});

test('jumlah penyetuju harus sesuai banyak level persetujuan', function () {
    $payload = pemesananPayload();
    $payload['penyetuju'] = [User::factory()->penyetuju()->create()->id];

    $this->post(route('pemesanan.store'), $payload)
        ->assertSessionHasErrors('penyetuju');
});

test('penyetuju yang sama untuk lebih dari satu level ditolak', function () {
    $penyetuju = User::factory()->penyetuju()->create();
    $payload = pemesananPayload();
    $payload['penyetuju'] = [$penyetuju->id, $penyetuju->id];

    $this->post(route('pemesanan.store'), $payload)
        ->assertSessionHasErrors('penyetuju');
});

test('admin tidak boleh menjadi pihak penyetuju', function () {
    $payload = pemesananPayload();
    $payload['penyetuju'] = [$this->admin->id, User::factory()->penyetuju()->create()->id];

    $this->post(route('pemesanan.store'), $payload)
        ->assertSessionHasErrors('penyetuju.0');
});

test('penyetuju harus memiliki role penyetuju', function () {
    $bukanPenyetuju = User::factory()->admin()->create();
    $payload = pemesananPayload();
    $payload['penyetuju'] = [$bukanPenyetuju->id, User::factory()->penyetuju()->create()->id];

    $this->post(route('pemesanan.store'), $payload)
        ->assertSessionHasErrors('penyetuju.0');
});
