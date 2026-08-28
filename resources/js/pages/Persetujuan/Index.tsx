import { Link } from '@inertiajs/react';
import AppLayout from '@/components/AppLayout';
import PersetujuanCard from '@/components/PersetujuanCard';
import type {PersetujuanRecord} from '@/types/domain';

export default function Index({ persetujuan }: { persetujuan: PersetujuanRecord[] }) {
    return (
        <AppLayout title="Daftar Antrian Persetujuan">
            <div className="mb-4 flex justify-end">
                <Link
                    href="/persetujuan/history"
                    className="text-sm font-medium text-blue-600 hover:text-blue-800"
                >
                    Riwayat persetujuan →
                </Link>
            </div>

            {persetujuan.length === 0 ? (
                <div className="rounded-2xl border bg-white p-10 text-center text-gray-500 shadow-sm">
                    Tidak ada persetujuan yang menunggu Anda.
                </div>
            ) : (
                <div className="space-y-4">
                    {persetujuan.map((record) => (
                        <PersetujuanCard key={record.id} record={record} />
                    ))}
                </div>
            )}
        </AppLayout>
    );
}
