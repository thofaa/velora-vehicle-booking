import { Link, useForm, usePage } from '@inertiajs/react';
import type {ReactNode} from 'react';

export default function AppLayout({
    title,
    children,
}: {
    title: string;
    children: ReactNode;
}) {
    const { auth, flash } = usePage().props as {
        auth: { user: { name?: string; role?: string } | null };
        flash?: { success?: string; error?: string };
    };
    const logout = useForm({});

    const isAdmin = auth.user?.role === 'admin';

    const nav = isAdmin
        ? [
              { href: '/dashboard', label: 'Dashboard' },
              { href: '/pemesanan', label: 'Pemesanan' },
              { href: '/pemesanan/create', label: '+ Buat Pemesanan' },
          ]
        : [
              { href: '/dashboard', label: 'Dashboard' },
              { href: '/persetujuan', label: 'Persetujuan' },
          ];

    return (
        <div className="min-h-screen bg-gray-50">
            <header className="sticky top-0 z-20 border-b bg-white/90 backdrop-blur">
                <div className="mx-auto flex max-w-6xl items-center justify-between px-4 py-3">
                    <Link href="/dashboard" className="text-lg font-semibold text-gray-900">
                        Vehicle Booking
                    </Link>
                    <nav className="flex items-center gap-1">
                        {nav.map((item) => (
                            <Link
                                key={item.href}
                                href={item.href}
                                className="rounded-lg px-3 py-2 text-sm font-medium text-gray-600 transition-colors hover:bg-gray-100 hover:text-gray-900"
                            >
                                {item.label}
                            </Link>
                        ))}
                        <span className="ml-2 hidden text-sm text-gray-500 sm:block">
                            {auth.user?.name}
                        </span>
                        <button
                            type="button"
                            onClick={() => logout.post('/logout')}
                            disabled={logout.processing}
                            className="ml-2 rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-100 disabled:opacity-60"
                        >
                            Keluar
                        </button>
                    </nav>
                </div>
            </header>

            {flash?.success && (
                <div className="mx-auto mt-4 max-w-6xl px-4">
                    <div className="rounded-lg bg-emerald-50 px-4 py-3 text-sm text-emerald-700 ring-1 ring-emerald-200">
                        {flash.success}
                    </div>
                </div>
            )}
            {flash?.error && (
                <div className="mx-auto mt-4 max-w-6xl px-4">
                    <div className="rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700 ring-1 ring-red-200">
                        {flash.error}
                    </div>
                </div>
            )}

            <main className="flex flex-col mx-auto max-w-6xl px-4 py-8 justify-center items-center">
                <h1 className="mb-6 text-2xl font-bold text-gray-900">{title}</h1>
                {children}
            </main>
        </div>
    );
}
