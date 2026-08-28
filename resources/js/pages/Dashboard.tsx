import { Head } from '@inertiajs/react';
import { useState } from 'react';
import Header from '@/components/Header';
import JadwalServiceCalendar from '@/components/JadwalServiceCalendar';
import KonsumsiBbmChart from '@/components/KonsumsiBbmChart';
import RiwayatPemakaianHeatmap from '@/components/RiwayatPemakaianHeatmap';
import type { KendaraanRingkas } from '@/types/dashboard';

interface Props {
    auth: { user: { name: string; role?: string } | null };
    kendaraan: KendaraanRingkas[];
}

const BULAN_NAMA = [
    'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
];

function WidgetCard({ title, description, children }: { title: string; description: string; children: React.ReactNode }) {
    return (
        <section className="rounded-2xl border bg-white p-5 shadow-sm dark:bg-gray-900 dark:border-gray-700">
            <h2 className="text-lg font-semibold text-gray-900 dark:text-gray-100">{title}</h2>
            <p className="mt-0.5 text-sm text-gray-500 dark:text-gray-400">{description}</p>
            <div className="mt-4">{children}</div>
        </section>
    );
}

export default function Dashboard({ auth, kendaraan }: Props) {
    const isAdmin = auth.user?.role === 'admin';
    const sekarang = new Date();
    const tahunSekarang = sekarang.getFullYear();
    const tahunOptions = [tahunSekarang - 1, tahunSekarang, tahunSekarang + 1];

    const [kategori, setKategori] = useState<'bulanan' | 'mingguan'>('bulanan');
    const [tahunBbm, setTahunBbm] = useState(tahunSekarang);
    const [bulanBbm, setBulanBbm] = useState(sekarang.getMonth() + 1);
    const [terpilih, setTerpilih] = useState<number[]>(kendaraan.slice(0, 3).map((k) => k.id));

    const [kendaraanPemakaian, setKendaraanPemakaian] = useState<number | null>(kendaraan[0]?.id ?? null);
    const [tahunPemakaian, setTahunPemakaian] = useState(tahunSekarang);

    const [kendaraanService, setKendaraanService] = useState<number | null>(kendaraan[0]?.id ?? null);
    const [bulanService, setBulanService] = useState(sekarang.getMonth() + 1);
    const [tahunService, setTahunService] = useState(tahunSekarang);

    const toggleKendaraan = (id: number) => {
        setTerpilih((sekarangTerpilih) => {
            if (sekarangTerpilih.includes(id)) {
                return sekarangTerpilih.filter((x) => x !== id);
            }

            if (sekarangTerpilih.length >= 5) {
                return sekarangTerpilih;
            }

            return [...sekarangTerpilih, id];
        });
    };

    if (!isAdmin) {
        const links = [
            { href: '/persetujuan', label: 'Persetujuan', desc: 'Proses persetujuan yang menunggu Anda' },
        ];

        return (
            <>
                <Head title="Dashboard" />
                <div className="flex min-h-screen flex-col bg-gray-50">
                    <Header />
                    <main className="flex flex-1 items-center justify-center p-6">
                        <div className="grid w-full max-w-md gap-4">
                            {links.map((link) => (
                                <a key={link.href} href={link.href} className="block rounded-2xl border bg-white p-5 transition hover:border-blue-300 hover:shadow-md">
                                    <p className="font-semibold text-gray-900">{link.label}</p>
                                    <p className="mt-1 text-sm text-gray-500">{link.desc}</p>
                                </a>
                            ))}
                        </div>
                    </main>
                </div>
            </>
        );
    }

    return (
        <>
            <Head title="Dashboard" />
            <div className="min-h-screen bg-gray-50">
                <Header />
                <main className="mx-auto max-w-6xl space-y-6 p-6">
                    <WidgetCard title="Konsumsi BBM" description="Total liter BBM per bulan/minggu untuk beberapa kendaraan">
                        <div className="flex flex-wrap items-center gap-3">
                            <div className="inline-flex rounded-lg bg-gray-100 p-1 dark:bg-gray-800">
                                {(['bulanan', 'mingguan'] as const).map((mode) => (
                                    <button
                                        key={mode}
                                        onClick={() => setKategori(mode)}
                                        className={`rounded-md px-3 py-1.5 text-sm font-medium ${
                                            kategori === mode
                                                ? 'bg-white text-indigo-600 shadow'
                                                : 'text-gray-600 hover:text-gray-900'
                                        }`}
                                    >
                                        {mode === 'bulanan' ? 'Bulanan' : 'Mingguan'}
                                    </button>
                                ))}
                            </div>

                            <select
                                value={tahunBbm}
                                onChange={(e) => setTahunBbm(Number(e.target.value))}
                                className="rounded-lg border px-2 py-1.5 text-sm"
                            >
                                {tahunOptions.map((tahun) => (
                                    <option key={tahun} value={tahun}>{tahun}</option>
                                ))}
                            </select>

                            {kategori === 'mingguan' && (
                                <select
                                    value={bulanBbm}
                                    onChange={(e) => setBulanBbm(Number(e.target.value))}
                                    className="rounded-lg border px-2 py-1.5 text-sm"
                                >
                                    {BULAN_NAMA.map((nama, index) => (
                                        <option key={nama} value={index + 1}>{nama}</option>
                                    ))}
                                </select>
                            )}
                        </div>

                        <div className="mt-3 flex flex-wrap gap-2">
                            {kendaraan.map((kendaraanItem) => {
                                const aktif = terpilih.includes(kendaraanItem.id);
                                const penuh = terpilih.length >= 5 && !aktif;

                                return (
                                    <button
                                        key={kendaraanItem.id}
                                        onClick={() => toggleKendaraan(kendaraanItem.id)}
                                        disabled={penuh}
                                        className={`rounded-full px-3 py-1 text-xs font-medium ring-1 ${
                                            aktif
                                                ? 'bg-indigo-50 text-indigo-700 ring-indigo-200'
                                                : 'bg-white text-gray-500 ring-gray-200 hover:ring-gray-400 disabled:opacity-40'
                                        }`}
                                    >
                                        {kendaraanItem.nomor_polisi} — {kendaraanItem.merk}
                                    </button>
                                );
                            })}
                        </div>

                        <div className="mt-4">
                            <KonsumsiBbmChart
                                kendaraanIds={terpilih}
                                kategori={kategori}
                                tahun={tahunBbm}
                                bulan={kategori === 'mingguan' ? bulanBbm : null}
                            />
                        </div>
                    </WidgetCard>

                    <WidgetCard title="Riwayat Pemakaian" description="Hari-hari kendaraan dipakai selama satu tahun">
                        <div className="flex flex-wrap items-center gap-3">
                            <select
                                value={kendaraanPemakaian ?? ''}
                                onChange={(e) => setKendaraanPemakaian(e.target.value ? Number(e.target.value) : null)}
                                className="rounded-lg border px-2 py-1.5 text-sm"
                            >
                                {kendaraan.map((kendaraanItem) => (
                                    <option key={kendaraanItem.id} value={kendaraanItem.id}>
                                        {kendaraanItem.nomor_polisi}
                                    </option>
                                ))}
                            </select>
                            <select
                                value={tahunPemakaian}
                                onChange={(e) => setTahunPemakaian(Number(e.target.value))}
                                className="rounded-lg border px-2 py-1.5 text-sm"
                            >
                                {tahunOptions.map((tahun) => (
                                    <option key={tahun} value={tahun}>{tahun}</option>
                                ))}
                            </select>
                        </div>
                        <div className="mt-4">
                            <RiwayatPemakaianHeatmap idKendaraan={kendaraanPemakaian} tahun={tahunPemakaian} />
                        </div>
                    </WidgetCard>

                    <WidgetCard title="Jadwal Service" description="Kalender service kendaraan dalam satu bulan">
                        <div className="flex flex-wrap items-center gap-3">
                            <select
                                value={kendaraanService ?? ''}
                                onChange={(e) => setKendaraanService(e.target.value ? Number(e.target.value) : null)}
                                className="rounded-lg border px-2 py-1.5 text-sm"
                            >
                                {kendaraan.map((kendaraanItem) => (
                                    <option key={kendaraanItem.id} value={kendaraanItem.id}>
                                        {kendaraanItem.nomor_polisi}
                                    </option>
                                ))}
                            </select>
                            <select
                                value={bulanService}
                                onChange={(e) => setBulanService(Number(e.target.value))}
                                className="rounded-lg border px-2 py-1.5 text-sm"
                            >
                                {BULAN_NAMA.map((nama, index) => (
                                    <option key={nama} value={index + 1}>{nama}</option>
                                ))}
                            </select>
                            <select
                                value={tahunService}
                                onChange={(e) => setTahunService(Number(e.target.value))}
                                className="rounded-lg border px-2 py-1.5 text-sm"
                            >
                                {tahunOptions.map((tahun) => (
                                    <option key={tahun} value={tahun}>{tahun}</option>
                                ))}
                            </select>
                        </div>
                        <div className="mt-4">
                            <JadwalServiceCalendar
                                idKendaraan={kendaraanService}
                                bulan={bulanService}
                                tahun={tahunService}
                            />
                        </div>
                    </WidgetCard>
                </main>
            </div>
        </>
    );
}