import { type ReactNode } from 'react';

export default function AuthInput({
    id,
    label,
    type = 'text',
    value,
    onChange,
    error,
    disabled,
    autoComplete,
    placeholder,
}: {
    id: string;
    label: string;
    type?: string;
    value: string;
    onChange: (value: string) => void;
    error?: string;
    disabled?: boolean;
    autoComplete?: string;
    placeholder?: string;
}) {
    const base =
        'w-full rounded-xl border bg-white/10 px-4 py-3 text-sm text-white placeholder:text-white/50 transition-all duration-200 outline-none backdrop-blur disabled:cursor-not-allowed disabled:opacity-60';
    const state = error
        ? 'border-red-400 focus:border-red-400 focus:ring-4 focus:ring-red-400/25'
        : 'border-white/25 focus:border-white focus:bg-white/15 focus:ring-4 focus:ring-white/20';

    return (
        <div className="space-y-1.5">
            <label htmlFor={id} className="block text-sm font-medium text-white/90">
                {label}
            </label>
            <input
                id={id}
                type={type}
                value={value}
                autoComplete={autoComplete}
                placeholder={placeholder}
                disabled={disabled}
                onChange={(e) => onChange(e.target.value)}
                className={`${base} ${state}`}
            />
            {error && <p className="text-xs text-red-300">{error}</p>}
        </div>
    );
}
