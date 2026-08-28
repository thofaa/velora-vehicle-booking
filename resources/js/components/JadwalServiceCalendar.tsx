import { useMemo } from 'react';
import { useJadwalService } from '@/lib/dashboardApi';
import type { JadwalServiceItem } from '@/types/dashboard';

interface Props {
    idKendaraan: number | null;
    bulan: number;
    tahun: number;
}

const NAMA_HARI = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];

const STATUS_STYLE: Record<JadwalServiceItem['status'], string> = {
    terjadwal: 'bg-amber-50 text-amber-700 ring-amber-200 dark:bg-amber-900/40 dark:text-amber-300 dark:ring-amber-800',
    selesai: 'bg-emerald-50 text-emerald-700 ring-emerald-200 dark:bg-emerald-900/40 dark:text-emerald-300 dark:ring-emerald-800',
    terlewat: 'bg-red-50 text-red-700 ring-red-200 dark:bg-red-900/40 dark:text-red-300 dark:ring-red-800',
};

export default function JadwalServiceCalendar({ idKendaraan, bulan, tahun }: Props) {
    const { data, isLoading, error } = useJadwalService(idKendaraan, bulan, tahun);

    const cells = useMemo(() => {
        if (!data) {
            return [];
        }

        const awal = new Date(tahun, bulan - 1, 1);
        const offset = (awal.getDay() + 6) % 7;
        const hariDalamBulan = new Date(tahun, bulan, 0).getDate();
        const jadwalPerHari = new Map<number, JadwalServiceItem>(data.jadwal.map((item) => [Number(item.tanggal.slice(-2)), item]));

        const total = Math.ceil((offset + hariDalamBulan) / 7) * 7;

        return Array.from({ length: total }, (_, index) => {
            const date = new Date(tahun, bulan - 1, index - offset + 1);
            const hari = date.getDate();

            return {
                tanggal: `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(hari).padStart(2, '0')}`,
                hari,
                dalamBulan: date.getMonth() === bulan - 1,
                service: jadwalPerHari.get(hari),
            };
        });
    }, [data, bulan, tahun]);

    if (isLoading) {
        return (
            <div className="animate-pulse rounded-lg bg-gray-200 p-2">
                <div className="grid grid-cols-7 gap-1">
                    {Array.from({ length: 35 }, (_, index) => (
                        <div key={index} className="h-12 rounded-md bg-gray-300"/>
                    ))}
                </div>
            </div>
        );
    }

    if (error || !data) {
        return <p className="text-sm text-red-600">Gagal memuat jadwal service.</p>;
    }

    return (
        <div>
            <p className="text-xs font-medium text-gray-700">
                {data.kendaraan
                    ? `${data.kendaraan.merk} ${data.kendaraan.tipe} — ${data.kendaraan.nomor_polisi}`
                    : `Kendaraan #${data.id_kendaraan}`}
            </p>

            <div className="mt-3 grid grid-cols-7 gap-1">
                {NAMA_HARI.map((hari) => (
                    <span key={hari} className="text-center text-[10px] font-medium text-gray-600">{hari}</span>
                ))}

                {cells.map(({ tanggal, hari, dalamBulan, service }) => {
                    const hariIni = tanggal === data.hari_ini;

                    return (
                        <div
                            key={tanggal}
                            className={`flex min-h-12 flex-col items-center justify-center rounded-md px-1 py-1 ${
                                dalamBulan
                                    ? 'bg-gray-400 ring-1 ring-gray-200'
                                    : 'bg-gray-200'
                            } ${hariIni ? 'ring-4 ring-indigo-500' : ''}`}
                        >
                            <span className="text-[13px] text-gray-800">
                                {hari}
                            </span>
                            {service && (
                                <span
                                    className={`mt-0.5 max-w-full truncate rounded px-1 py-0.5 text-[10px] ring-1 ${STATUS_STYLE[service.status]}`}
                                    title={`${service.jenis} — ${service.status}`}
                                >
                                    {service.jenis}
                                </span>
                            )}
                        </div>
                    );
                })}
            </div>
        </div>
    );
}