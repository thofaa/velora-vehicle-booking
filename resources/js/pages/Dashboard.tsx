import { Head } from '@inertiajs/react';
import Header from '@/components/Header';

export default function Dashboard({
    auth,
}: {
    auth: { user: { name: string; role?: string } | null };
}) {
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
                <Header />
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
