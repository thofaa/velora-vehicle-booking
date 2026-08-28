import { useForm } from '@inertiajs/react';
import { useState } from 'react';
import { STATUS_BADGE, STATUS_LABEL  } from '@/types/domain';
import type {PersetujuanRecord} from '@/types/domain';
import { buttonClasses, errorClasses } from './formClasses';

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

export default function PersetujuanCard({ record }: { record: PersetujuanRecord }) {
    const approve = useForm({});
    const reject = useForm({ catatan: '' });
    const { pemesanan } = record;
    const [showReject, setShowReject] = useState(false);

    if (!pemesanan) {
return null;
}

    const previousLevels = record.level_sebelumnya ?? [];
    const previousBlocked = previousLevels.some((l) => l.status !== 'approved');
    const bookingStopped = ['ditolak', 'dibatalkan'].includes(pemesanan.status);
    const actionable = !previousBlocked && !bookingStopped;

    return (
        <div className="rounded-2xl border bg-white p-5 shadow-sm w-[600px]">
            <div className="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <p className="text-base font-semibold text-gray-900">
                        Level {record.level_persetujuan}{' '}
                        <span
                            className={`ml-2 rounded-full px-2.5 py-1 text-xs font-semibold ${STATUS_BADGE[record.status]}`}
                        >
                            {STATUS_LABEL[record.status]}
                        </span>
                    </p>
                    <p className="mt-2 text-sm text-gray-600">
                        Kendaraan: {pemesanan.kendaraan?.nomor_polisi} — {pemesanan.kendaraan?.merk}{' '}
                        {pemesanan.kendaraan?.tipe}
                    </p>
                    <p className="mt-1 text-sm text-gray-600">
                        Driver: {pemesanan.driver?.nama}
                    </p>
                    <p className="mt-1 text-sm text-gray-600">
                        Tanggal: {formatDate(pemesanan.tanggal_mulai)} s.d {formatDate(pemesanan.tanggal_selesai)}
                    </p>
                    <p className="mt-1 text-sm text-gray-600">
                        Status: {STATUS_LABEL[pemesanan.status]}
                    </p>
                </div>
                <div className="text-right text-sm">
                    {previousLevels.length > 0 && (
                        <p className="text-gray-500">
                            Level sebelumnya:{' '}
                            {previousLevels.map((l) => STATUS_LABEL[l.status]).join(', ')}
                        </p>
                    )}
                    {record.catatan && (
                        <p className="mt-1 text-gray-500">Catatan: {record.catatan}</p>
                    )}
                </div>
            </div>

            {actionable ? (
                <div className="mt-4 flex flex-wrap items-center gap-3 border-t pt-4">
                    <button
                        type="button"
                        disabled={approve.processing}
                        onClick={() =>
                            approve.post(`/persetujuan/${record.id}/approve`, {
                                preserveScroll: true,
                            })
                        }
                        className={buttonClasses}
                    >
                        {approve.processing ? 'Menyetujui...' : 'Setujui'}
                    </button>
                    <button
                        type="button"
                        onClick={() => setShowReject((v) => !v)}
                        className="rounded-lg border border-red-200 bg-red-50 px-4 py-2.5 text-sm font-semibold text-red-700 hover:bg-red-100"
                    >
                        {showReject ? 'Batal' : 'Tolak'}
                      </button>
                    {showReject && (
                        <form
                            onSubmit={(e) => {
                                e.preventDefault();
                                reject.post(`/persetujuan/${record.id}/reject`, {
                                    preserveScroll: true,
                                    onSuccess: () => setShowReject(false),
                                });
                            }}
                            className="flex w-full items-end gap-2"
                        >
                            <div className="flex-1">
                                <input
                                    type="text"
                                    value={reject.data.catatan}
                                    placeholder="Catatan penolakan (wajib)"
                                    onChange={(e) => reject.setData('catatan', e.target.value)}
                                    className="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm outline-none focus:border-red-400 focus:ring-2 focus:ring-red-400/30"
                                />
                                {reject.errors.catatan && (
                                    <p className={errorClasses}>{reject.errors.catatan}</p>
                                )}
                            </div>
                            <button
                                type="submit"
                                disabled={reject.processing}
                                className="rounded-lg bg-red-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-red-700 disabled:opacity-60"
                            >
                                {reject.processing ? 'Menyimpan...' : 'Simpan'}
                            </button>
                        </form>
                    )}
                </div>
            ) : (
                <p className="mt-4 border-t pt-4 text-sm text-gray-500">
                    Tidak dapat diproses: proses persetujuan ini{' '}
                    {bookingStopped ? 'sudah berhenti' : 'menunggu level sebelumnya'}.
                </p>
            )}
        </div>
    );
}
