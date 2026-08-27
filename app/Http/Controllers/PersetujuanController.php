<?php

namespace App\Http\Controllers;

use App\Http\Requests\ApprovePersetujuanRequest;
use App\Http\Requests\RejectPersetujuanRequest;
use App\Http\Resources\PersetujuanResource;
use App\Models\Persetujuan;
use App\Services\PemesananStatusService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class PersetujuanController extends Controller
{
    public function __construct(
        private readonly PemesananStatusService $statusService,
    ) {}

    /**
     * Daftar persetujuan pending yang menjadi tanggung jawab penyetuju.
     */
    public function index(Request $request): Response
    {
        $data = $this->listing($request->user()->id, [Persetujuan::STATUS_PENDING]);

        return Inertia::render('Persetujuan/Index', [
            'persetujuan' => PersetujuanResource::collection($data)->resolve(),
        ]);
    }

    /**
     * Riwayat persetujuan yang sudah diproses oleh penyetuju (read-only).
     */
    public function history(Request $request): Response
    {
        $data = $this->listing($request->user()->id, [
            Persetujuan::STATUS_APPROVED,
            Persetujuan::STATUS_REJECTED,
            Persetujuan::STATUS_DIBATALKAN,
        ]);

        return Inertia::render('Persetujuan/History', [
            'persetujuan' => PersetujuanResource::collection($data)->resolve(),
        ]);
    }

    /**
     * Setujui persetujuan tertentu.
     */
    public function approve(ApprovePersetujuanRequest $request, Persetujuan $persetujuan): RedirectResponse
    {
        DB::transaction(function () use ($persetujuan) {
            $persetujuan->update([
                'status' => 'approved',
                'approved_at' => now(),
            ]);

            $this->statusService->refresh($persetujuan->pemesanan);
        });

        return Redirect::back()->with('success', 'Persetujuan berhasil disetujui.');
    }

    /**
     * Tolak persetujuan tertentu disertai catatan.
     */
    public function reject(RejectPersetujuanRequest $request, Persetujuan $persetujuan): RedirectResponse
    {
        DB::transaction(function () use ($request, $persetujuan) {
            $persetujuan->update([
                'status' => 'rejected',
                'catatan' => $request->validated('catatan'),
            ]);

            $this->statusService->refresh($persetujuan->pemesanan);
        });

        return Redirect::back()->with('success', 'Persetujuan ditolak.');
    }

    /**
     * @param  array<int, string>  $status
     */
    private function listing(int $userId, array $status)
    {
        return Persetujuan::query()
            ->where('id_pihak_penyetuju', $userId)
            ->whereIn('status', $status)
            ->with(['pemesanan' => fn ($q) => $q->with(['kendaraan', 'driver', 'admin', 'persetujuan'])])
            ->orderBy('level_persetujuan')
            ->orderByDesc('created_at')
            ->get();
    }
}
