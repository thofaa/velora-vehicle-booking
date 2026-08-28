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
}: {
    driver: DriverOption[];
    value: number | null;
    onChange: (id: number | null) => void;
    unavailableIds: number[];
    disabled: boolean;
    error?: string;
}) {
    const allUnavailable =
        driver.length > 0 && unavailableIds.length === driver.length;

    return (
        <div>
            <label htmlFor="driver" className={labelClasses}>
                Driver
            </label>
            <div className="relative">
                <select
                    id="driver"
                    className={`${selectClasses}`}
                    value={value ?? ''}
                    disabled={disabled}
                    onChange={(e) => onChange(e.target.value ? Number(e.target.value) : null)}
                >
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
                {value === null && (
                    <span
                        className={`pointer-events-none absolute left-3 top-1/2 z-10 -translate-y-1/2 text-sm ${
                            !disabled && allUnavailable ? 'text-orange-500' : 'text-gray-400'
                        }`}
                    >
                        {!disabled && allUnavailable
                            ? 'Semua driver tidak tersedia pada tanggal tersebut'
                            : ''}
                    </span>
                )}
            </div>
            {error && <p className={errorClasses}>{error}</p>}
        </div>
    );
}
