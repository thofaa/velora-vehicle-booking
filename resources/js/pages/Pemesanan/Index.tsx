import gsap from 'gsap';
import { useEffect, useRef, useState } from 'react';
import AppLayout from '@/components/AppLayout';
import { STATUS_BADGE, STATUS_LABEL  } from '@/types/domain';
import type {PemesananRecord} from '@/types/domain';

function formatDate(value: string | null | undefined): string {
    if (!value) {
return '-';
}

    return new Intl.DateTimeFormat('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    }).format(new Date(value));
}

function today(): string {
    return new Date().toISOString().slice(0, 10);
}

function monthStart(): string {
    const [tahun, bulan] = today().split('-').map(Number);

    return `${tahun}-${String(bulan).padStart(2, '0')}-01`;
}

function monthEnd(): string {
    const [tahun, bulan] = today().split('-').map(Number);
    const end = new Date(tahun, bulan, 0).getDate();

    return `${tahun}-${String(bulan).padStart(2, '0')}-${String(end).padStart(2, '0')}`;
}

export default function Index({ pemesanan }: { pemesanan: PemesananRecord[] }) {
    const [showCatatan, setShowCatatan] = useState(false);
    const [dari, setDari] = useState(monthStart);
    const [hingga, setHingga] = useState(monthEnd);
    const bodyRef = useRef<HTMLTableSectionElement>(null);
    const wrapRef = useRef<HTMLDivElement>(null);

    const exportHref = dari !== '' && hingga !== ''
        ? `/pemesanan/export?dari=${dari}&hingga=${hingga}`
        : '#';

    useEffect(() => {
        const cells = Array.from(
            bodyRef.current?.querySelectorAll<HTMLElement>('.col-catatan') ?? []
        );
        const wrap = wrapRef.current;

        if (!cells.length || !wrap) {
            return;
        }

        if (showCatatan) {
            gsap.to(wrap, {
                width: 1280,
                duration: 0.45,
                ease: 'power2.out',
            });
            gsap.fromTo(
                cells,
                { maxWidth: 0, opacity: 0 },
                {
                    maxWidth: 320,
                    opacity: 1,
                    duration: 0.45,
                    ease: 'power2.out',
                    onComplete: () =>
                        cells.forEach((el) => {
                            el.style.maxWidth = '';
                            el.classList.remove('whitespace-nowrap');
                        }),
                }
            );
        } else {
            gsap.to(wrap, {
                width: 1000,
                duration: 0.35,
                ease: 'power2.inOut',
            });
            gsap.to(cells, {
                maxWidth: 0,
                opacity: 0,
                duration: 0.35,
                ease: 'power2.inOut',
                onStart: () =>
                    cells.forEach((el) => {
                        el.style.maxWidth = '320px';
                        el.classList.add('whitespace-nowrap');
                    }),
            });
        }
    }, [showCatatan]);

    return (
        <AppLayout title="Daftar Pemesanan">
            <div className="mb-4 flex flex-wrap items-center gap-3 rounded-2xl border bg-white p-4 shadow-sm">
                <label className="flex items-center gap-2 text-sm text-gray-600">
                    Dari
                    <input
                        type="date"
                        value={dari}
                        onChange={(e) => setDari(e.target.value)}
                        className="rounded-lg border border-gray-300 px-2 py-1.5 text-sm"
                    />
                </label>
                <label className="flex items-center gap-2 text-sm text-gray-600">
                    Sampai
                    <input
                        type="date"
                        value={hingga}
                        onChange={(e) => setHingga(e.target.value)}
                        className="rounded-lg border border-gray-300 px-2 py-1.5 text-sm"
                    />
                </label>
                <a
                    href={exportHref}
                    className="ml-auto rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-indigo-700 disabled:opacity-50"
                    aria-disabled={exportHref === '#'}
                >
                    Export Excel
                </a>
            </div>
            <div ref={wrapRef} className="w-250 overflow-x-auto rounded-2xl border bg-white shadow-sm">
                <table className="min-w-full divide-y divide-gray-200 text-sm">
                    <thead className="bg-gray-50">
                        <tr>
                            <th className="px-4 py-3 text-center font-semibold text-gray-600">Kendaraan</th>
                            <th className="px-4 py-3 text-center font-semibold text-gray-600">Driver</th>
                            <th className="px-4 py-3 text-center font-semibold text-gray-600">Rentang Waktu</th>
                            <th className="px-4 py-3 text-center font-semibold text-gray-600">Rantai Persetujuan</th>
                            <th className="px-4 py-3 text-center font-semibold text-gray-600">Status</th>
                            <th className="px-4 py-3 text-center font-semibold text-gray-600">
                                    <button
                                        type="button"
                                        onClick={() => setShowCatatan((v) => !v)}
                                        title={showCatatan ? 'Sembunyikan kolom Catatan' : 'Tampilkan kolom Catatan'}
                                        className="inline-flex items-center gap-1 -ml-1 rounded px-1 py-0.5 hover:bg-gray-200"
                                    >
                                        {showCatatan ? (
                                            <svg viewBox="0 0 16 16" fill="currentColor" className="h-4 w-4">
                                                <path
                                                    fillRule="evenodd"
                                                    d="M15 2a1 1 0 0 0-1-1H2a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1zM0 2a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2zm11.5 5.5a.5.5 0 0 1 0 1H5.707l2.147 2.146a.5.5 0 0 1-.708.708l-3-3a.5.5 0 0 1 0-.708l3-3a.5.5 0 1 1 .708.708L5.707 7.5z"
                                                />
                                            </svg>
                                        ) : (
                                            <svg viewBox="0 0 16 16" fill="currentColor" className="h-4 w-4">
                                                <path
                                                    fillRule="evenodd"
                                                    d="M15 2a1 1 0 0 0-1-1H2a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1zM0 2a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2zm4.5 5.5a.5.5 0 0 0 0 1h5.793l-2.147 2.146a.5.5 0 0 0 .708.708l3-3a.5.5 0 0 0 0-.708l-3-3a.5.5 0 1 0-.708.708L10.293 7.5z"
                                                />
                                            </svg>
                                        )}
                                        {showCatatan && 'Catatan'}
                                    </button>
                                </th>
                        </tr>
                    </thead>
                    <tbody ref={bodyRef} className="divide-y divide-gray-100">
                        {pemesanan.map((p) => (
                            <tr key={p.id} className="hover:bg-gray-50">
                                <td className="px-4 py-3 font-medium text-gray-900">
                                    {p.kendaraan?.nomor_polisi ?? '-'}
                                    <span className="block text-xs text-gray-500">
                                        {p.kendaraan?.merk} {p.kendaraan?.tipe}
                                    </span>
                                </td>
                                <td className="px-4 py-3">{p.driver?.nama ?? '-'}</td>
                                <td className="px-4 py-3">
                                    <span className="block">{formatDate(p.tanggal_mulai)}</span>
                                    <span className="block text-xs text-gray-500">s.d {formatDate(p.tanggal_selesai)}</span>
                                </td>
                                <td className="px-4 py-3">
                                    {(p.persetujuan ?? []).length ? (
                                        <ol className="list-decimal space-y-1 pl-4">
                                            {p.persetujuan?.map((a) => (
                                                <li key={a.id} className="flex items-center gap-2">
                                                    <span className="text-gray-700">
                                                        {a.penyetuju?.name ?? 'Level '.concat(String(a.level_persetujuan))}
                                                    </span>
                                                    <span
                                                        className={`rounded-full px-2 py-0.5 text-xs font-medium ${STATUS_BADGE[a.status]}`}
                                                    >
                                                        {STATUS_LABEL[a.status]}
                                                    </span>
                                                </li>
                                            ))}
                                        </ol>
                                    ) : (
                                        <span className="text-gray-400">-</span>
                                    )}
                                </td>
                                <td className="px-4 py-3">
                                    <span
                                        className={`rounded-full px-2.5 py-1 text-xs font-semibold ${STATUS_BADGE[p.status]}`}
                                    >
                                        {STATUS_LABEL[p.status]}
                                    </span>
                                </td>
                                <td
                                    className="col-catatan whitespace-nowrap px-4 py-3"
                                    style={{ maxWidth: 0, opacity: 0, overflow: 'hidden' }}
                                >
                                    {(p.persetujuan ?? []).some((a) => a.catatan) ? (
                                        <div className="space-y-1">
                                            {p.persetujuan?.filter((a) => a.catatan).map((a) => (
                                                <div
                                                    key={a.id}
                                                    className="inline-block w-fit max-w-[320px] rounded-lg bg-amber-50 px-3 py-1.5 ring-1 ring-gray-400"
                                                >
                                                    <p className="text-sm text-gray-700">
                                                        <span className="font-medium">
                                                            {a.penyetuju?.name ?? 'Level '.concat(String(a.level_persetujuan))}:
                                                        </span>{' '}
                                                        {a.catatan}
                                                    </p>
                                                </div>
                                            ))}
                                        </div>
                                    ) : (
                                        <span className="text-gray-400">-</span>
                                    )}
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
                {pemesanan.length === 0 && (
                    <p className="px-4 py-6 text-center text-gray-500">Belum ada pemesanan.</p>
                )}
            </div>
        </AppLayout>
    );
}
