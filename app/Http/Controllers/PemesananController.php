<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExportPemesananRequest;
use App\Http\Requests\KetersediaanQueryRequest;
use App\Http\Requests\StorePemesananRequest;
use App\Http\Resources\PemesananResource;
use App\Models\Driver;
use App\Models\Kendaraan;
use App\Models\Pemesanan;
use App\Models\Persetujuan;
use App\Models\User;
use App\Services\KetersediaanPemesananService;
use App\Services\PemesananExportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PemesananController extends Controller
{
    public function __construct(
        private readonly KetersediaanPemesananService $ketersediaan,
        private readonly PemesananExportService $exportService,
    ) {}

    /**
     * Daftar pemesanan (admin).
     */
    public function index(Request $request): Response
    {
        $pemesanan = Pemesanan::query()
            ->with(['kendaraan', 'driver', 'admin', 'persetujuan.pihakPenyetuju'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        return Inertia::render('Pemesanan/Index', [
            'pemesanan' => PemesananResource::collection($pemesanan)->resolve(),
        ]);
    }

    /**
     * Tampilkan form pembuatan pemesanan (admin).
     */
    public function create(Request $request): Response
    {
        return Inertia::render('Pemesanan/Create', [
            'kendaraan' => Kendaraan::aktif()->orderBy('merk')->get([
                'id',
                'nomor_polisi',
                'merk',
                'tipe',
                'banyak_level_persetujuan',
            ]),
            'driver' => Driver::aktif()->orderBy('nama')->get(['id', 'nama', 'nomor_telepon']),
            'penyetuju' => User::penyetuju()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Simpan pemesanan baru beserta record persetujuan (admin).
     */
    public function store(StorePemesananRequest $request): RedirectResponse
    {
        $data = $request->safe()->only(['tanggal_mulai', 'tanggal_selesai', 'id_kendaraan', 'id_driver']);
        $penyetuju = $request->validated('penyetuju');

        $mulai = Carbon::parse($data['tanggal_mulai']);
        $selesai = Carbon::parse($data['tanggal_selesai']);

        $pemesanan = DB::transaction(function () use ($data, $penyetuju, $mulai, $selesai, $request) {
            // Ponytail: guard akhir terhadap race; tingkatkan ke constraint DB bila throughput menuntut.
            if (! $this->ketersediaan->kendaraanTersedia((int) $data['id_kendaraan'], $mulai, $selesai)) {
                throw ValidationException::withMessages([
                    'id_kendaraan' => 'Kendaraan ini sudah dipesan pada rentang tanggal tersebut.',
                ]);
            }

            if (! $this->ketersediaan->driverTersedia((int) $data['id_driver'], $mulai, $selesai)) {
                throw ValidationException::withMessages([
                    'id_driver' => 'Driver ini sudah bertugas pada rentang tanggal tersebut.',
                ]);
            }

            $pemesanan = Pemesanan::create([
                ...$data,
                'id_admin' => $request->user()->id,
                'status' => 'menunggu_persetujuan',
            ]);

            foreach ($penyetuju as $level => $userId) {
                Persetujuan::create([
                    'id_pemesanan' => $pemesanan->id,
                    'level_persetujuan' => $level + 1,
                    'id_pihak_penyetuju' => $userId,
                    'status' => 'pending',
                ]);
            }

            return $pemesanan;
        });

        return Redirect::route('pemesanan.index')->with('success', 'Pemesanan berhasil dibuat.');
    }

    /**
     * Unduh laporan pemesanan periodik (.xlsx) pada rentang tanggal mulai.
     */
    public function export(ExportPemesananRequest $request): StreamedResponse
    {
        $dari = Carbon::parse($request->validated('dari'));
        $hingga = Carbon::parse($request->validated('hingga'));

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray($this->exportService->header(), null, 'A1');
        $sheet->fromArray($this->exportService->data($dari, $hingga), null, 'A2');
        $sheet->getStyle('A1:'.$sheet->getHighestColumn().'1')->getFont()->setBold(true);

        foreach (range('A', $sheet->getHighestColumn()) as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $filename = sprintf(
            'laporan-pemesanan_%s_%s.xlsx',
            $dari->format('Y-m-d'),
            $hingga->format('Y-m-d'),
        );

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Daftar kendaraan & driver yang tersedia pada rentang tanggal (admin).
     */
    public function ketersediaan(KetersediaanQueryRequest $request): JsonResponse
    {
        $mulai = Carbon::parse($request->validated('tanggal_mulai'));
        $selesai = Carbon::parse($request->validated('tanggal_selesai'));

        $kendaraanTersedia = Kendaraan::aktif()
            ->get()
            ->filter(fn (Kendaraan $k) => $this->ketersediaan->kendaraanTersedia($k->id, $mulai, $selesai))
            ->pluck('id');

        $driverTersedia = Driver::aktif()
            ->get()
            ->filter(fn (Driver $d) => $this->ketersediaan->driverTersedia($d->id, $mulai, $selesai))
            ->pluck('id');

        return response()->json([
            'kendaraan_tersedia' => $kendaraanTersedia->values(),
            'driver_tersedia' => $driverTersedia->values(),
        ]);
    }
}
