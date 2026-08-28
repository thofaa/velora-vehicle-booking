import {
    Bar,
    BarChart,
    CartesianGrid,
    Legend,
    ResponsiveContainer,
    Tooltip,
    XAxis,
    YAxis,
} from 'recharts';
import { useKonsumsiBbm } from '@/lib/dashboardApi';
import type { KendaraanRingkas } from '@/types/dashboard';

interface Props {
    kendaraanIds: number[] | null;
    kategori: 'bulanan' | 'mingguan';
    tahun: number;
    bulan: number | null;
}

const PALETTE = ['#6366f1', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'];

export default function KonsumsiBbmChart({ kendaraanIds, kategori, tahun, bulan }: Props) {
    const { data, isLoading, error } = useKonsumsiBbm({ kendaraanIds, kategori, tahun, bulan });

    if (isLoading) {
        return (
            <div className="h-80 animate-pulse rounded-lg bg-gray-200">
                <div className="flex h-full items-end gap-2 p-3">
                    {Array.from({ length: 12 }, (_, index) => (
                        <div
                            key={index}
                            className="flex-1 rounded-t bg-gray-300"
                            style={{ height: `${35 + ((index * 41) % 55)}%` }}
                        />
                    ))}
                </div>
            </div>
        );
    }

    if (error || !data) {
        return <p className="text-sm text-red-600">Gagal memuat data konsumsi BBM.</p>;
    }

    const warnaKendaraan = new Map<KendaraanRingkas['id'], string>(
        data.kendaraan.map((k, index) => [k.id, PALETTE[index % PALETTE.length]]),
    );

    return (
        <div className="h-80">
            <ResponsiveContainer width="100%" height="100%">
                <BarChart data={data.data} margin={{ top: 8, right: 8, left: -12, bottom: 0 }}>
                    <CartesianGrid strokeDasharray="3 3" stroke="#e5e7eb" />
                    <XAxis dataKey="bucket" tick={{ fontSize: 12 }} />
                    <YAxis tick={{ fontSize: 12 }} />
                    <Tooltip cursor={{ fill: 'rgba(99,102,241,0.08)' }} />
                    <Legend />
                    {data.kendaraan.map((kendaraan) => (
                        <Bar
                            key={kendaraan.id}
                            dataKey={String(kendaraan.id)}
                            name={`${kendaraan.merk} ${kendaraan.tipe} (${kendaraan.nomor_polisi})`}
                            fill={warnaKendaraan.get(kendaraan.id)}
                            radius={[4, 4, 0, 0]}
                        />
                    ))}
                </BarChart>
            </ResponsiveContainer>
        </div>
    );
}