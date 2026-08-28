import { Link, useForm, usePage } from '@inertiajs/react';

export default function Header() {
    const { auth } = usePage().props as {
        auth: { user: { name?: string; role?: string } | null };
    };
    const logout = useForm({});

    const isAdmin = auth.user?.role === 'admin';

    const nav = isAdmin
        ? [
              { href: '/dashboard', label: 'Dashboard' },
              { href: '/pemesanan', label: 'Daftar Pemesanan' },
              { href: '/pemesanan/create', label: 'Buat Pemesanan' },
          ]
        : [
              { href: '/dashboard', label: 'Dashboard' },
              { href: '/persetujuan', label: 'Persetujuan' },
          ];

    return (
        <header className="sticky top-0 z-20 border-b border-gray-100 bg-white/95 backdrop-blur">
            <div className="mx-auto flex max-w-6xl items-center justify-between px-6 py-3">
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
    );
}