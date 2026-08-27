import { errorClasses, labelClasses, selectClasses } from './formClasses';

export type PenyetujuOption = {
    id: number;
    name: string;
};

export default function PenyetujuLevelSelect({
    level,
    penyetuju,
    value,
    onChange,
    usedIds,
    error,
}: {
    level: number;
    penyetuju: PenyetujuOption[];
    value: number | null;
    onChange: (id: number | null) => void;
    usedIds: number[];
    error?: string;
}) {
    return (
        <div>
            <label htmlFor={`penyetuju-${level}`} className={labelClasses}>
                Penyetuju Level {level}
            </label>
            <select
                id={`penyetuju-${level}`}
                className={selectClasses}
                value={value ?? ''}
                onChange={(e) => onChange(e.target.value ? Number(e.target.value) : null)}
            >
                <option value="">Pilih penyetuju level {level}</option>
                {penyetuju.map((u) => {
                    const used = usedIds.includes(u.id);

                    return (
                        <option key={`${level}-${u.id}`} value={u.id} disabled={used}>
                            {u.name}
                            {used ? ' (terpakai)' : ''}
                        </option>
                    );
                })}
            </select>
            {error && <p className={errorClasses}>{error}</p>}
        </div>
    );
}
