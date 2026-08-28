import { Link } from '@inertiajs/react';
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

export default function Index({ pemesanan }: { pemesanan: PemesananRecord[] }) {
    const [showCatatan, setShowCatatan] = useState(false);
    const bodyRef = useRef<HTMLTableSectionElement>(null);

    useEffect(() => {
        const cells = Array.from(
            bodyRef.current?.querySelectorAll<HTMLElement>('.col-catatan') ?? []
        );

        if (!cells.length) {
            return;
        }

        if (showCatatan) {
            gsap.fromTo(
                cells,
                { maxWidth: 0, opacity: 0 },
                {
                    maxWidth: 600,
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
            gsap.to(cells, {
                maxWidth: 0,
                opacity: 0,
                duration: 0.35,
                ease: 'power2.inOut',
                onStart: () =>
                    cells.forEach((el) => {
                        el.style.maxWidth = '600px';
                        el.classList.add('whitespace-nowrap');
                    }),
            });
        }
    }, [showCatatan]);

    return (
        <AppLayout title="Daftar Pemesanan">
            <div className="mb-4 flex justify-end">
                <Link
                    href="/pemesanan/create"
                    className="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700"
                >
                    + Buat Pemesanan
                </Link>
            </div>

            <div className="w-250 overflow-x-auto rounded-2xl border bg-white shadow-sm">
                <table className="min-w-full divide-y divide-gray-200 text-sm">
                    <thead className="bg-gray-50">
                        <tr>
                            <th className="px-4 py-3 text-left font-semibold text-gray-600">Kendaraan</th>
                            <th className="px-4 py-3 text-left font-semibold text-gray-600">Driver</th>
                            <th className="px-4 py-3 text-left font-semibold text-gray-600">Rentang Waktu</th>
                            <th className="px-4 py-3 text-left font-semibold text-gray-600">Rantai Persetujuan</th>
                            <th className="px-4 py-3 text-left font-semibold text-gray-600">Status</th>
                            <th className="px-4 py-3 text-left font-semibold text-gray-600">
                                    <button
                                        type="button"
                                        onClick={() => setShowCatatan((v) => !v)}
                                        title={showCatatan ? 'Sembunyikan kolom Catatan' : 'Tampilkan kolom Catatan'}
                                        className="inline-flex items-center gap-1 -ml-1 rounded px-1 py-0.5 hover:bg-gray-200"
                                    >
                                        {showCatatan && 'Catatan'}
                                        <svg
                                            viewBox="0 0 20 20"
                                            fill="currentColor"
                                            className={`h-4 w-4 transition-transform ${showCatatan ? '' : '-rotate-90'}`}
                                        >
                                            <path
                                                fillRule="evenodd"
                                                d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z"
                                                clipRule="evenodd"
                                            />
                                        </svg>
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
                                                <p key={a.id} className="text-sm text-gray-600">
                                                    <span className="font-medium">
                                                        {a.penyetuju?.name ?? 'Level '.concat(String(a.level_persetujuan))}:
                                                    </span>{' '}
                                                    {a.catatan}
                                                </p>
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
