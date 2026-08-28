import { Fragment, useMemo } from 'react';
import { useRiwayatPemakaian } from '@/lib/dashboardApi';
import type { HariPemakaian } from '@/types/dashboard';

interface Props {
    idKendaraan: number | null;
    tahun: number;
}

const NAMA_HARI = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];
const BULAN = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

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
        return (
            <div className="animate-pulse rounded-lg bg-gray-200 p-2">
                <div className="grid grid-cols-7 gap-1">
                    {Array.from({ length: 35 }, (_, index) => (
                        <div key={index} className="h-3 rounded-[2px] bg-gray-300"/>
                    ))}
                </div>
            </div>
        );
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
                    <span className="flex items-center gap-1.5 text-gray-700">
                        <span className="h-3 w-3 rounded-sm bg-indigo-500"/> dipakai
                    </span>
                    <span className="flex items-center gap-1.5 text-gray-700">
                        <span className="h-3 w-3 rounded-sm bg-gray-500"/> tidak
                    </span>
                </div>
            </div>

            <div className="mt-3">
                <div
                    className="grid gap-[1px] bg-white p-2"
                    style={{ gridTemplateColumns: `auto repeat(${rows.length}, minmax(0, 1fr))` }}
                >
                    <span/>
                    {(() => {
                        let lastMonth = -1;

                        return rows.map((week, weekIndex) => {
                            const firstDay = week.find(Boolean);
                            const month = firstDay ? new Date(`${firstDay.tanggal}T00:00:00`).getMonth() : lastMonth;
                            const show = month !== lastMonth;

                            if (show) {
                                lastMonth = month;
                            }

                            return (
                                <span
                                    key={`week-header-${weekIndex}`}
                                    className="whitespace-nowrap text-center text-[10px] text-gray-600"
                                >
                                    {show ? BULAN[month] : ''}
                                </span>
                            );
                        });
                    })()}
                    {NAMA_HARI.map((hariName, weekdayIndex) => (
                        <Fragment key={hariName}>
                            <span className="text-center pr-1 text-[10px] text-gray-600">{hariName}</span>
                            {rows.map((week, weekIndex) => {
                                const hari = week[weekdayIndex];

                                if (!hari) {
                                    return <span key={`${weekIndex}-${weekdayIndex}`} className="h-3"/>;
                                }

                                const tanggal = new Date(`${hari.tanggal}T00:00:00`);

                                return (
                                    <span
                                        key={hari.tanggal}
                                        title={`${tanggal.toLocaleDateString('id-ID')} — ${hari.dipakai ? 'dipakai' : 'tidak dipakai'}`}
                                        className={`h-3 rounded-[1px] ${hari.dipakai ? 'bg-indigo-500' : 'bg-gray-500'}`}
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