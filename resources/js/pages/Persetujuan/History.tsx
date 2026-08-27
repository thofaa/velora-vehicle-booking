import { Link } from '@inertiajs/react';
import AppLayout from '@/components/AppLayout';
import { STATUS_BADGE, STATUS_LABEL  } from '@/types/domain';
import type {PersetujuanRecord} from '@/types/domain';

function formatDate(value?: string | null): string {
    if (!value) {
return '-';
}

    return new Intl.DateTimeFormat('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    }).format(new Date(value));
}

export default function History({ persetujuan }: { persetujuan: PersetujuanRecord[] }) {
    return (
        <AppLayout title="Riwayat Persetujuan">
            <div className="mb-4 flex justify-end">
                <Link
                    href="/persetujuan"
                    className="text-sm font-medium text-blue-600 hover:text-blue-800"
                >
                    ← Kembali ke pending
                </Link>
            </div>

            {persetujuan.length === 0 ? (
                <div className="rounded-2xl border bg-white p-10 text-center text-gray-500 shadow-sm">
                    Belum ada riwayat persetujuan.
                </div>
            ) : (
                <div className="space-y-4">
                    {persetujuan.map((record) => (
                        <div
                            key={record.id}
                            className="rounded-2xl border bg-white p-5 shadow-sm"
                        >
                            <div className="flex flex-wrap items-center justify-between gap-3">
                                <p className="text-sm font-medium text-gray-700">
                                    Level {record.level_persetujuan}
                                    <span
                                        className={`ml-2 rounded-full px-2.5 py-1 text-xs font-semibold ${STATUS_BADGE[record.status]}`}
                                    >
                                        {STATUS_LABEL[record.status]}
                                    </span>
                                </p>
                                {record.approved_at && (
                                    <p className="text-xs text-gray-400">
                                        Diproses {formatDate(record.approved_at)}
                                    </p>
                                )}
                            </div>
                            {record.pemesanan?.kendaraan && (
                                <p className="mt-2 text-sm text-gray-600">
                                    {record.pemesanan.kendaraan.nomor_polisi} —{' '}
                                    {record.pemesanan.kendaraan.merk}{' '}
                                    {record.pemesanan.kendaraan.tipe}
                                    <span className="text-gray-400">
                                        {' '}
                                        · {formatDate(record.pemesanan.tanggal_mulai)} s.d{' '}
                                        {formatDate(record.pemesanan.tanggal_selesai)}
                                    </span>
                                </p>
                            )}
                            {record.catatan && (
                                <p className="mt-2 rounded-lg bg-gray-50 px-3 py-2 text-sm text-gray-700">
                                    Catatan: {record.catatan}
                                </p>
                            )}
                        </div>
                    ))}
                </div>
            )}
        </AppLayout>
    );
}
