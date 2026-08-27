import { errorClasses, labelClasses, selectClasses } from './formClasses';

export type DriverOption = {
    id: number;
    nama: string;
    nomor_telepon: string;
};

export default function DriverSelect({
    driver,
    value,
    onChange,
    unavailableIds,
    disabled,
    error,
    loading,
}: {
    driver: DriverOption[];
    value: number | null;
    onChange: (id: number | null) => void;
    unavailableIds: number[];
    disabled: boolean;
    error?: string;
    loading?: boolean;
}) {
    return (
        <div>
            <label htmlFor="driver" className={labelClasses}>
                Driver
            </label>
            <select
                id="driver"
                className={selectClasses}
                value={value ?? ''}
                disabled={disabled}
                onChange={(e) => onChange(e.target.value ? Number(e.target.value) : null)}
            >
                <option value="">
                    {loading ? 'Memuat driver tersedia...' : 'Pilih driver'}
                </option>
                {driver.map((d) => {
                    const unavailable = unavailableIds.includes(d.id);

                    return (
                        <option key={d.id} value={d.id} disabled={unavailable}>
                            {d.nama} — {d.nomor_telepon}
                            {unavailable ? ' (tidak tersedia)' : ''}
                        </option>
                    );
                })}
            </select>
            {error && <p className={errorClasses}>{error}</p>}
        </div>
    );
}
