import { errorClasses, labelClasses, selectClasses } from './formClasses';

export type KendaraanOption = {
    id: number;
    nomor_polisi: string;
    merk: string;
    tipe: string;
    banyak_level_persetujuan: number;
};

export default function KendaraanSelect({
    kendaraan,
    value,
    onChange,
    unavailableIds,
    disabled,
    error,
    loading,
}: {
    kendaraan: KendaraanOption[];
    value: number | null;
    onChange: (id: number | null) => void;
    unavailableIds: number[];
    disabled: boolean;
    error?: string;
    loading?: boolean;
}) {
    return (
        <div>
            <label htmlFor="kendaraan" className={labelClasses}>
                Kendaraan
            </label>
            <select
                id="kendaraan"
                className={selectClasses}
                value={value ?? ''}
                disabled={disabled}
                onChange={(e) => onChange(e.target.value ? Number(e.target.value) : null)}
            >
                {kendaraan.map((k) => {
                    const unavailable = unavailableIds.includes(k.id);

                    return (
                        <option key={k.id} value={k.id} disabled={unavailable}>
                            {k.nomor_polisi} — {k.merk} {k.tipe}
                            {unavailable ? ' (tidak tersedia)' : ''}
                        </option>
                    );
                })}
            </select>
            {error && <p className={errorClasses}>{error}</p>}
        </div>
    );
}
