import { useForm } from '@inertiajs/react';
import { Head } from '@inertiajs/react';

export default function Dashboard({
    auth,
}: {
    auth: { user: { name: string; role?: string } | null };
}) {
    const { post, processing } = useForm({});
    const isAdmin = auth.user?.role === 'admin';

    const links = isAdmin
        ? [
              { href: '/pemesanan', label: 'Daftar Pemesanan', desc: 'Kelola pemesanan kendaraan' },
              { href: '/pemesanan/create', label: 'Buat Pemesanan', desc: 'Ajukan pemesanan kendaraan baru' },
          ]
        : [
              { href: '/persetujuan', label: 'Persetujuan', desc: 'Proses persetujuan yang menunggu Anda' },
          ];

    return (
        <>
            <Head title="Dashboard" />
            <div className="flex min-h-screen flex-col bg-gray-50">
                <header className="flex items-center justify-between border-b bg-white px-6 py-4">
                    <h1 className="text-lg font-semibold">Dashboard</h1>
                    <div className="flex items-center gap-4">
                        <span className="text-sm text-gray-600">
                            Halo, {auth.user?.name ?? 'Pengguna'}
                        </span>
                        <button
                            type="button"
                            onClick={() => post('/logout')}
                            disabled={processing}
                            className="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium hover:bg-gray-100 disabled:opacity-60"
                        >
                            Keluar
                        </button>
                    </div>
                </header>
                <main className="flex flex-1 items-center justify-center p-6">
                    <div className="grid w-full max-w-md gap-4">
                        {links.map((link) => (
                            <a
                                key={link.href}
                                href={link.href}
                                className="block rounded-2xl border bg-white p-5 transition hover:border-blue-300 hover:shadow-md"
                            >
                                <p className="font-semibold text-gray-900">{link.label}</p>
                                <p className="mt-1 text-sm text-gray-500">{link.desc}</p>
                            </a>
                        ))}
                    </div>
                </main>
            </div>
        </>
    );
}
