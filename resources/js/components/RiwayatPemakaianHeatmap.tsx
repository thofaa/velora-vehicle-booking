import { Fragment, useMemo } from 'react';
import { useRiwayatPemakaian } from '@/lib/dashboardApi';
import type { HariPemakaian } from '@/types/dashboard';

interface Props {
    idKendaraan: number | null;
    tahun: number;
}

const NAMA_HARI = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];

const mondayIndex = (date: Date) => (date.getDay() + 6) % 7;

export default function RiwayatPemakaianHeatmap({ idKendaraan, tahun }: Props) {
    const { data, isLoading, error } = useRiwayatPemakaian(idKendaraan, tahun);

    const rows = useMemo(() => {
        if (!data) {
            return [];
        }

        const offset = mondayIndex(new Date(`${tahun}-01-01`));
        const totalRows = Math.ceil((data.hari.length + offset) / 7);

        const grid: Array<Array<HariPemakaian | null>> = Array.from({ length: totalRows }, () =>
            Array.from({ length: 7 }, () => null),
        );

        data.hari.forEach((hari, index) => {
            const tanggal = new Date(`${hari.tanggal}T00:00:00`);
            const col = mondayIndex(tanggal);
            const row = Math.floor((index + offset) / 7);
            grid[row][col] = hari;
        });

        return grid;
    }, [data, tahun]);

    if (isLoading) {
        return <div className="animate-pulse space-y-3">
            <div className="h-72 rounded-lg bg-gray-100 dark:bg-gray-800"/>
        </div>;
    }

    if (error || !data) {
        return <p className="text-sm text-red-600">Gagal memuat riwayat pemakaian.</p>;
    }

    return (
        <div>
            <div className="flex flex-wrap items-center justify-between gap-3 text-xs text-gray-500 dark:text-gray-400">
                <p className="font-medium text-gray-700 dark:text-gray-700">
                    {data.kendaraan
                        ? `${data.kendaraan.merk} ${data.kendaraan.tipe} — ${data.kendaraan.nomor_polisi}`
                        : `Kendaraan #${data.tahun}`}
                </p>
                <div className="flex items-center gap-3">
                    <span className="flex items-center gap-1.5">
                        <span className="h-3 w-3 rounded-sm bg-indigo-500"/> dipakai
                    </span>
                    <span className="flex items-center gap-1.5">
                        <span className="h-3 w-3 rounded-sm border border-gray-200 bg-gray-100 dark:border-gray-700 dark:bg-gray-800"/> tidak
                    </span>
                </div>
            </div>

            <div className="mt-3 overflow-x-auto">
                <div className="inline-grid grid-cols-[auto_repeat(7,minmax(12px,1fr))] gap-1">
                    <span/>
                    {NAMA_HARI.map((hari) => (
                        <span key={hari} className="text-center text-[10px] text-gray-600">{hari}</span>
                    ))}
                    {rows.map((week, weekIndex) => (
                        <Fragment key={weekIndex}>
                            <span className="pr-2 text-[10px] text-gray-600">
                                {weekIndex % 4 === 0 ? `Minggu ${weekIndex + 1}` : ''}
                            </span>
                            {week.map((hari, col) => {
                                if (!hari) {
                                    return <span key={`${weekIndex}-${col}`} className="h-3"/>;
                                }

                                const tanggal = new Date(`${hari.tanggal}T00:00:00`);

                                return (
                                    <span
                                        key={hari.tanggal}
                                        title={`${tanggal.toLocaleDateString('id-ID')} — ${hari.dipakai ? 'dipakai' : 'tidak dipakai'}`}
                                        className={`h-3 w-3 rounded-sm ${hari.dipakai ? 'bg-indigo-500' : 'bg-gray-500'}`}
                                    />
                                );
                            })}
                        </Fragment>
                    ))}
                </div>
            </div>
        </div>
    );
}