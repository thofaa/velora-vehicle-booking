<?php

use App\Models\Pemesanan;
use App\Models\Persetujuan;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

function buatPemesananDuaLevel(array $overrides = []): array
{
    $p1 = User::factory()->penyetuju()->create();
    $p2 = User::factory()->penyetuju()->create();
    $admin = User::factory()->admin()->create();

    $pemesanan = Pemesanan::factory()->create([
        'id_admin' => $admin->id,
        'status' => 'menunggu_persetujuan',
    ]);

    $l1 = Persetujuan::factory()->create([
        'id_pemesanan' => $pemesanan->id,
        'level_persetujuan' => 1,
        'id_pihak_penyetuju' => $p1->id,
        'status' => 'pending',
    ]);
    $l2 = Persetujuan::factory()->create([
        'id_pemesanan' => $pemesanan->id,
        'level_persetujuan' => 2,
        'id_pihak_penyetuju' => $p2->id,
        'status' => 'pending',
    ]);

    return compact('pemesanan', 'p1', 'p2', 'admin', 'l1', 'l2');
}

beforeEach(function () {
    $this->penyetuju = User::factory()->penyetuju()->create();
});

test('penyetuju hanya melihat persetujuan pending miliknya', function () {
    buatPemesananDuaLevel();

    $this->actingAs($this->penyetuju)->get(route('persetujuan.index'))->assertOk();
});

test('bukan penyetuju tidak dapat mengakses persetujuan', function () {
    User::factory()->admin()->create();
    $this->actingAs(User::factory()->admin()->create());

    $this->get(route('persetujuan.index'))->assertForbidden();
});

test('level 1 dapat disetujui dan pemesanan tetap menunggu level 2', function () {
    $item = buatPemesananDuaLevel();
    $this->actingAs($item['p1']);

    $this->post(route('persetujuan.approve', $item['l1']))
        ->assertRedirect();

    expect($item['l1']->fresh()->status)->toBe('approved')
        ->and($item['l1']->fresh()->approved_at)->not->toBeNull()
        ->and($item['pemesanan']->fresh()->status)->toBe('menunggu_persetujuan');
});

test('semua level disetujui menjadikan pemesanan disetujui', function () {
    $item = buatPemesananDuaLevel();
    $item['l1']->update(['status' => 'approved', 'approved_at' => now()]);
    $this->actingAs($item['p2']);

    $this->post(route('persetujuan.approve', $item['l2']))->assertRedirect();

    expect($item['pemesanan']->fresh()->status)->toBe('disetujui');
});

test('level 2 tidak dapat disetujui sebelum level 1', function () {
    $item = buatPemesananDuaLevel();
    $this->actingAs($item['p2']);

    $this->post(route('persetujuan.approve', $item['l2']))
        ->assertSessionHasErrors('persetujuan')
        ->assertRedirect();

    expect($item['l2']->fresh()->status)->toBe('pending');
});

test('tolak mengisi catatan dan mengubah pemesanan menjadi ditolak', function () {
    $item = buatPemesananDuaLevel();
    $this->actingAs($item['p1']);

    $this->post(route('persetujuan.reject', $item['l1']), ['catatan' => 'Dokumen kurang'])
        ->assertRedirect();

    expect($item['l1']->fresh()->status)->toBe('rejected')
        ->and($item['l1']->fresh()->catatan)->toBe('Dokumen kurang')
        ->and($item['pemesanan']->fresh()->status)->toBe('ditolak');
});

test('tolak mewajibkan catatan', function () {
    $item = buatPemesananDuaLevel();
    $this->actingAs($item['p1']);

    $this->post(route('persetujuan.reject', $item['l1']))
        ->assertSessionHasErrors('catatan');
});

test('pemesanan yang ditolak tidak muncul di daftar pending', function () {
    $item = buatPemesananDuaLevel();
    $this->actingAs($item['p1']);
    $this->post(route('persetujuan.reject', $item['l1']), ['catatan' => 'Dokumen kurang'])->assertRedirect();

    expect($item['pemesanan']->fresh()->status)->toBe('ditolak');

    $this->actingAs($item['p2']);
    $this->get(route('persetujuan.index'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('persetujuan', 0)
        );
});

test('record yang sudah diproses tidak dapat diproses ulang', function () {
    $item = buatPemesananDuaLevel();
    $item['l1']->update(['status' => 'approved', 'approved_at' => now()]);
    $this->actingAs($item['p1']);

    $this->post(route('persetujuan.approve', $item['l1']))
        ->assertSessionHasErrors('persetujuan');
});

test('user bukan pemilik tidak dapat memproses', function () {
    $item = buatPemesananDuaLevel();
    $penyerobot = User::factory()->penyetuju()->create();
    $this->actingAs($penyerobot);

    $this->post(route('persetujuan.approve', $item['l1']))->assertForbidden();
});

test('pemesanan yang dibatalkan tidak dapat diproses', function () {
    $item = buatPemesananDuaLevel();
    $item['pemesanan']->update(['status' => 'dibatalkan']);
    $this->actingAs($item['p1']);

    $this->post(route('persetujuan.approve', $item['l1']))
        ->assertSessionHasErrors('persetujuan');
});
