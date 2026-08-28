<?php

use App\Models\JadwalService;
use App\Models\Kendaraan;
use App\Models\KonsumsiBbm;
use App\Models\Pemesanan;
use App\Models\User;
use Illuminate\Support\Carbon;
use Inertia\Testing\AssertableInertia;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
});

test('penyetuju tidak dapat mengakses endpoint dashboard', function () {
    $penyetuju = User::factory()->penyetuju()->create();
    $this->actingAs($penyetuju);

    $this->get(route('dashboard.konsumsi-bbm', [
        'kendaraan_ids' => [1],
        'kategori' => 'bulanan',
        'tahun' => 2026,
    ]))->assertForbidden();

    $this->get(route('dashboard.riwayat-pemakaian', ['id_kendaraan' => 1, 'tahun' => 2026]))
        ->assertForbidden();

    $this->get(route('dashboard.jadwal-service', ['id_kendaraan' => 1, 'bulan' => 6, 'tahun' => 2026]))
        ->assertForbidden();
});

test('penyetuju dialihkan dari halaman dashboard ke persetujuan', function () {
    $this->actingAs(User::factory()->penyetuju()->create());

    $this->get(route('dashboard'))->assertRedirect(route('persetujuan.index'));
});

test('admin dapat mengakses halaman dashboard dengan daftar kendaraan', function () {
    Kendaraan::factory()->count(2)->create();

    $this->actingAs($this->admin)->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Dashboard')
            ->has('kendaraan', 2));
});

test('konsumsi bbm bulanan mengelompokkan total liter per bulan per kendaraan', function () {
    $kendaraanA = Kendaraan::factory()->create(['nomor_polisi' => 'B 1 ABC']);
    $kendaraanB = Kendaraan::factory()->create(['nomor_polisi' => 'B 2 DEF']);

    KonsumsiBbm::factory()->create([
        'id_kendaraan' => $kendaraanA->id,
        'tanggal' => '2026-01-15',
        'jumlah_liter' => 40,
    ]);
    KonsumsiBbm::factory()->create([
        'id_kendaraan' => $kendaraanA->id,
        'tanggal' => '2026-01-20',
        'jumlah_liter' => 60,
    ]);
    KonsumsiBbm::factory()->create([
        'id_kendaraan' => $kendaraanB->id,
        'tanggal' => '2026-03-10',
        'jumlah_liter' => 25.5,
    ]);

    $this->actingAs($this->admin)->getJson(route('dashboard.konsumsi-bbm', [
        'kendaraan_ids' => [$kendaraanA->id, $kendaraanB->id],
        'kategori' => 'bulanan',
        'tahun' => 2026,
    ]))
        ->assertOk()
        ->assertJson([
            'kategori' => 'bulanan',
            'tahun' => 2026,
            'bulan' => null,
        ])
        ->assertJsonCount(2, 'kendaraan')
        ->assertJsonCount(12, 'data')
        ->assertJsonPath('data.0.bucket', 'Jan')
        ->assertJsonPath("data.0.{$kendaraanA->id}", 100)
        ->assertJsonPath('data.2.bucket', 'Mar')
        ->assertJsonPath("data.2.{$kendaraanB->id}", 25.5);
});

test('konsumsi bbm mingguan mengelompokkan sesuai minggu ke-(hari/7) dalam bulan', function () {
    $kendaraan = Kendaraan::factory()->create();

    KonsumsiBbm::factory()->create([
        'id_kendaraan' => $kendaraan->id,
        'tanggal' => '2026-02-03',
        'jumlah_liter' => 20,
    ]);
    KonsumsiBbm::factory()->create([
        'id_kendaraan' => $kendaraan->id,
        'tanggal' => '2026-02-10',
        'jumlah_liter' => 30,
    ]);
    KonsumsiBbm::factory()->create([
        'id_kendaraan' => $kendaraan->id,
        'tanggal' => '2026-02-28',
        'jumlah_liter' => 50,
    ]);

    $this->actingAs($this->admin)->getJson(route('dashboard.konsumsi-bbm', [
        'kendaraan_ids' => [$kendaraan->id],
        'kategori' => 'mingguan',
        'tahun' => 2026,
        'bulan' => 2,
    ]))
        ->assertOk()
        ->assertJsonPath('bulan', 2)
        ->assertJsonCount(4, 'data')
        ->assertJsonPath('data.0.bucket', 'Minggu 1')
        ->assertJsonPath("data.0.{$kendaraan->id}", 20)
        ->assertJsonPath('data.1.bucket', 'Minggu 2')
        ->assertJsonPath("data.1.{$kendaraan->id}", 30)
        ->assertJsonPath('data.3.bucket', 'Minggu 4')
        ->assertJsonPath("data.3.{$kendaraan->id}", 50);
});

test('konsumsi bbm memvalidasi parameternya', function () {
    $this->actingAs($this->admin)->getJson(route('dashboard.konsumsi-bbm', [
        'kendaraan_ids' => [1, 2, 3, 4, 5, 6],
        'kategori' => 'bulanan',
        'tahun' => 2026,
    ]))->assertUnprocessable();

    $this->actingAs($this->admin)->getJson(route('dashboard.konsumsi-bbm', [
        'kendaraan_ids' => [1],
        'kategori' => 'harian',
        'tahun' => 2026,
    ]))->assertUnprocessable();

    $this->actingAs($this->admin)->getJson(route('dashboard.konsumsi-bbm', [
        'kendaraan_ids' => [1],
        'kategori' => 'mingguan',
        'tahun' => 2026,
    ]))->assertUnprocessable();
});

test('riwayat pemakaian menandai hari sesuai pemesanan disetujui saja', function () {
    $kendaraan = Kendaraan::factory()->create();
    $pemesanan = Pemesanan::factory()->create([
        'id_kendaraan' => $kendaraan->id,
        'status' => 'disetujui',
        'tanggal_mulai' => '2026-06-01',
        'tanggal_selesai' => '2026-06-05',
    ]);

    Pemesanan::factory()->create([
        'id_kendaraan' => $kendaraan->id,
        'status' => 'dibatalkan',
        'tanggal_mulai' => '2026-07-10',
        'tanggal_selesai' => '2026-07-15',
    ]);

    $indexJuniDua = 31 + 28 + 31 + 30 + 31; // 1 Juni = index ke-151 (0-based)

    $this->actingAs($this->admin)->getJson(route('dashboard.riwayat-pemakaian', [
        'id_kendaraan' => $kendaraan->id,
        'tahun' => 2026,
    ]))
        ->assertOk()
        ->assertJsonPath('tahun', 2026)
        ->assertJsonCount(365, 'hari')
        ->assertJsonPath("hari.{$indexJuniDua}.dipakai", true)
        ->assertJsonPath("hari.{$indexJuniDua}.id_pemesanan", $pemesanan->id)
        ->assertJsonPath('hari.0.dipakai', false)
        ->assertJsonPath('hari.0.id_pemesanan', null);
});

test('jadwal service menandai kegiatan terjadwal yang lewat sebagai terlewat', function () {
    Carbon::setTestNow('2026-06-15 10:00:00');
    $kendaraan = Kendaraan::factory()->create();

    JadwalService::factory()->create([
        'id_kendaraan' => $kendaraan->id,
        'tanggal_service' => '2026-06-12',
        'jenis_service' => 'Ganti oli',
        'status' => 'terjadwal',
    ]);
    JadwalService::factory()->create([
        'id_kendaraan' => $kendaraan->id,
        'tanggal_service' => '2026-06-20',
        'jenis_service' => 'Servis rutin',
        'status' => 'terjadwal',
    ]);
    JadwalService::factory()->create([
        'id_kendaraan' => $kendaraan->id,
        'tanggal_service' => '2026-06-05',
        'jenis_service' => 'Rotasi ban',
        'status' => 'selesai',
    ]);

    $this->actingAs($this->admin)->getJson(route('dashboard.jadwal-service', [
        'id_kendaraan' => $kendaraan->id,
        'bulan' => 6,
        'tahun' => 2026,
    ]))
        ->assertOk()
        ->assertJsonCount(3, 'jadwal')
        ->assertJsonPath('jadwal.0.status', 'selesai')
        ->assertJsonPath('jadwal.1.status', 'terlewat')
        ->assertJsonPath('jadwal.2.status', 'terjadwal')
        ->assertJsonPath('jadwal.1.jenis', 'Ganti oli');
});

afterEach(function () {
    Carbon::setTestNow(null);
});
