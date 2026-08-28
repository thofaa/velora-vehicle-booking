<?php

namespace App\Http\Controllers;

use App\Http\Requests\Dashboard\JadwalServiceQueryRequest;
use App\Http\Requests\Dashboard\KonsumsiBbmQueryRequest;
use App\Http\Requests\Dashboard\RiwayatPemakaianQueryRequest;
use App\Models\Kendaraan;
use App\Services\JadwalServiceService;
use App\Services\KonsumsiBbmService;
use App\Services\RiwayatPemakaianService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        private readonly KonsumsiBbmService $konsumsiBbmService,
        private readonly RiwayatPemakaianService $riwayatPemakaianService,
        private readonly JadwalServiceService $jadwalServiceService,
    ) {}

    /**
     * Halaman dashboard, membawa daftar kendaraan untuk filter widget.
     */
    public function index(): Response|RedirectResponse
    {
        if (auth()->user()?->role !== 'admin') {
            return redirect()->route('persetujuan.index');
        }

        return Inertia::render('Dashboard', [
            'kendaraan' => Kendaraan::aktif()->orderBy('merk')->get([
                'id', 'nomor_polisi', 'merk', 'tipe',
            ]),
        ]);
    }

    /**
     * Total konsumsi BBM per bucket waktu (per bulan atau per minggu) untuk beberapa kendaraan.
     */
    public function konsumsiBbm(KonsumsiBbmQueryRequest $request): JsonResponse
    {
        $validated = $request->validated();

        return response()->json($this->konsumsiBbmService->data(
            array_map('intval', $validated['kendaraan_ids']),
            $validated['kategori'],
            (int) $validated['tahun'],
            isset($validated['bulan']) ? (int) $validated['bulan'] : null,
        ));
    }

    /**
     * Riwayat pemakaian harian suatu kendaraan selama satu tahun.
     */
    public function riwayatPemakaian(RiwayatPemakaianQueryRequest $request): JsonResponse
    {
        $validated = $request->validated();

        return response()->json($this->riwayatPemakaianService->data(
            (int) $validated['id_kendaraan'],
            (int) $validated['tahun'],
        ));
    }

    /**
     * Jadwal service suatu kendaraan pada satu bulan, status terlewat dihitung.
     */
    public function jadwalService(JadwalServiceQueryRequest $request): JsonResponse
    {
        $validated = $request->validated();

        return response()->json($this->jadwalServiceService->data(
            (int) $validated['id_kendaraan'],
            (int) $validated['bulan'],
            (int) $validated['tahun'],
        ));
    }
}
