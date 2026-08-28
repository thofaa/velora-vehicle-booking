<?php

use App\Models\Driver;
use App\Models\Kendaraan;
use App\Models\Pemesanan;
use App\Models\Persetujuan;
use App\Models\User;
use App\Services\PemesananExportService;
use Illuminate\Support\Carbon;

function pemesananPayload(array $overrides = []): array
{
    return array_merge([
        'tanggal_mulai' => now()->addDays(1)->format('Y-m-d'),
        'tanggal_selesai' => now()->addDays(2)->format('Y-m-d'),
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
        'tanggal_mulai' => now()->addDays(2)->format('Y-m-d'),
        'tanggal_selesai' => now()->addDays(1)->format('Y-m-d'),
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

test('penyetuju tidak dapat mengunduh laporan pemesanan', function () {
    $this->actingAs(User::factory()->penyetuju()->create());

    $this->get(route('pemesanan.export', ['dari' => '2026-01-01', 'hingga' => '2026-01-31']))
        ->assertForbidden();
});

test('export memerlukan rentang tanggal yang valid', function () {
    $this->get(route('pemesanan.export', ['dari' => '2026-02-01', 'hingga' => '2026-01-01']))
        ->assertSessionHasErrors('hingga');

    $this->get(route('pemesanan.export'))
        ->assertSessionHasErrors(['dari', 'hingga']);
});

test('admin dapat mengunduh laporan pemesanan xlsx pada rentang tanggal', function () {
    Pemesanan::factory()->create([
        'id_admin' => $this->admin->id,
        'tanggal_mulai' => '2026-01-10',
        'tanggal_selesai' => '2026-01-12',
    ]);

    Pemesanan::factory()->create([
        'id_admin' => $this->admin->id,
        'tanggal_mulai' => '2026-02-01',
        'tanggal_selesai' => '2026-02-02',
    ]);

    $this->get(route('pemesanan.export', ['dari' => '2026-01-01', 'hingga' => '2026-01-31']))
        ->assertOk()
        ->assertHeader(
            'content-type',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        )
        ->assertHeader(
            'content-disposition',
            'attachment; filename=laporan-pemesanan_2026-01-01_2026-01-31.xlsx'
        );
});

test('laporan pemesanan berisi rantai persetujuan dan catatan penolakan', function () {
    $penyetuju1 = User::factory()->penyetuju()->create(['name' => 'Rina']);
    $penyetuju2 = User::factory()->penyetuju()->create(['name' => 'Sari']);

    $pemesanan = Pemesanan::factory()->create([
        'id_admin' => $this->admin->id,
        'tanggal_mulai' => '2026-01-10',
        'tanggal_selesai' => '2026-01-12',
    ]);

    Persetujuan::factory()->create([
        'id_pemesanan' => $pemesanan->id,
        'id_pihak_penyetuju' => $penyetuju1->id,
        'level_persetujuan' => 1,
        'status' => 'approved',
    ]);
    Persetujuan::factory()->create([
        'id_pemesanan' => $pemesanan->id,
        'id_pihak_penyetuju' => $penyetuju2->id,
        'level_persetujuan' => 2,
        'status' => 'rejected',
        'catatan' => 'Kendaraan tidak tersedia',
    ]);

    $rows = app(PemesananExportService::class)->data(
        Carbon::parse('2026-01-01'),
        Carbon::parse('2026-01-31'),
    );

    expect($rows)->toHaveCount(1)
        ->and($rows[0][12])->toBe('Level 1 - Rina (Disetujui); Level 2 - Sari (Ditolak)')
        ->and($rows[0][13])->toBe('Sari: Kendaraan tidak tersedia');
});
