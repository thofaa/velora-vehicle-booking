import { useForm } from '@inertiajs/react';
import { useEffect, useMemo, useState } from 'react';
import DriverSelect from './DriverSelect';
import type {DriverOption} from './DriverSelect';
import { buttonClasses, errorClasses, labelClasses } from './formClasses';
import KendaraanSelect from './KendaraanSelect';
import type {KendaraanOption} from './KendaraanSelect';
import PenyetujuLevelSelect from './PenyetujuLevelSelect';
import type {PenyetujuOption} from './PenyetujuLevelSelect';

type Props = {
    kendaraan: KendaraanOption[];
    driver: DriverOption[];
    penyetuju: PenyetujuOption[];
};

export default function PemesananForm({ kendaraan, driver, penyetuju }: Props) {
    const { data, setData, post, errors, processing } = useForm({
        tanggal_mulai: '',
        tanggal_selesai: '',
        id_kendaraan: null as number | null,
        id_driver: null as number | null,
        penyetuju: [] as (number | null)[],
    });

    const [availability, setAvailability] = useState<{
        key: string;
        kendaraan: number[];
        driver: number[];
    } | null>(null);

    const datesKey =
        data.tanggal_mulai && data.tanggal_selesai
            ? `${data.tanggal_mulai}|${data.tanggal_selesai}`
            : '';

    const selectedKendaraan = useMemo(
        () => kendaraan.find((k) => k.id === data.id_kendaraan) ?? null,
        [kendaraan, data.id_kendaraan],
    );
    const levels = selectedKendaraan?.banyak_level_persetujuan ?? 0;

    const availabilityDerived = useMemo(() => {
        if (!availability || availability.key !== datesKey) {
            return {
                loading: Boolean(datesKey),
                unavailableKendaraan: [] as number[],
                unavailableDriver: [] as number[],
            };
        }

        return {
            loading: false,
            unavailableKendaraan: kendaraan
                .filter((k) => !availability.kendaraan.includes(k.id))
                .map((k) => k.id),
            unavailableDriver: driver
                .filter((d) => !availability.driver.includes(d.id))
                .map((d) => d.id),
        };
    }, [kendaraan, driver, availability, datesKey]);

    const { loading, unavailableKendaraan, unavailableDriver } = availabilityDerived;

    useEffect(() => {
        if (!datesKey) {
            return;
        }

        const controller = new AbortController();
        fetch(
            `/pemesanan/ketersediaan?tanggal_mulai=${encodeURIComponent(
                data.tanggal_mulai,
            )}&tanggal_selesai=${encodeURIComponent(data.tanggal_selesai)}`,
            { signal: controller.signal },
        )
            .then((r) => r.json())
            .then(
                (json: {
                    kendaraan_tersedia: number[];
                    driver_tersedia: number[];
                }) => {
                    setAvailability({
                        key: datesKey,
                        kendaraan: json.kendaraan_tersedia,
                        driver: json.driver_tersedia,
                    });
                },
            )
            .catch(() => {});

        return () => controller.abort();
    }, [datesKey, data.tanggal_mulai, data.tanggal_selesai]);

    const pickKendaraan = (id: number | null) => {
        setData('id_kendaraan', id);
        setData('penyetuju', []);

        if (!id) {
            setData('id_driver', null);
        }
    };

    const onLevelChange = (index: number, id: number | null) => {
        const next = Array.from({ length: levels }, (_, i) => data.penyetuju[i] ?? null);
        next[index] = id;
        setData('penyetuju', next);
    };

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        post('/pemesanan');
    };

    return (
        <form onSubmit={submit} className="space-y-6">
            <div className="grid gap-5 sm:grid-cols-2">
                <div>
                    <label htmlFor="tanggal_mulai" className={labelClasses}>
                        Tanggal Mulai
                    </label>
                    <input
                        id="tanggal_mulai"
                        type="datetime-local"
                        className="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/30"
                        value={data.tanggal_mulai}
                        onChange={(e) => setData('tanggal_mulai', e.target.value)}
                    />
                    {errors.tanggal_mulai && <p className={errorClasses}>{errors.tanggal_mulai}</p>}
                </div>
                <div>
                    <label htmlFor="tanggal_selesai" className={labelClasses}>
                        Tanggal Selesai
                    </label>
                    <input
                        id="tanggal_selesai"
                        type="datetime-local"
                        className="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/30"
                        value={data.tanggal_selesai}
                        onChange={(e) => setData('tanggal_selesai', e.target.value)}
                    />
                    {errors.tanggal_selesai && (
                        <p className={errorClasses}>{errors.tanggal_selesai}</p>
                    )}
                </div>
            </div>

            <KendaraanSelect
                kendaraan={kendaraan}
                value={data.id_kendaraan}
                onChange={pickKendaraan}
                unavailableIds={unavailableKendaraan}
                disabled={!data.tanggal_mulai || !data.tanggal_selesai || loading}
                loading={loading}
                error={errors.id_kendaraan}
            />

            <DriverSelect
                driver={driver}
                value={data.id_driver}
                onChange={(id) => setData('id_driver', id)}
                unavailableIds={unavailableDriver}
                disabled={!data.id_kendaraan}
                loading={loading}
                error={errors.id_driver}
            />

            <div className="space-y-4">
                <p className="text-sm font-medium text-gray-700">Rantai Persetujuan</p>
                {levels === 0 ? (
                    <p className="text-sm text-gray-500">
                        Pilih kendaraan untuk menentukan jumlah level persetujuan.
                    </p>
                ) : (
                    Array.from({ length: levels }, (_, i) => (
                        <PenyetujuLevelSelect
                            key={i}
                            level={i + 1}
                            penyetuju={penyetuju}
                            value={data.penyetuju[i] ?? null}
                            onChange={(id) => onLevelChange(i, id)}
                            usedIds={data.penyetuju.filter((v): v is number => v !== null)}
                            error={errors[`penyetuju.${i}`]}
                        />
                    ))
                )}
                {errors.penyetuju && <p className={errorClasses}>{errors.penyetuju}</p>}
            </div>

            <div className="flex items-center gap-3">
                <button type="submit" disabled={processing} className={buttonClasses}>
                    {processing ? 'Menyimpan...' : 'Buat Pemesanan'}
                </button>
            </div>
        </form>
    );
}
