import { useForm } from '@inertiajs/react';
import { Head } from '@inertiajs/react';

export default function Dashboard({ auth }: { auth: { user: { name: string } | null } }) {
    const { post, processing } = useForm({});

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
                    <div className="max-w-md rounded-2xl border bg-white p-8 text-center shadow-sm">
                        <p className="text-sm text-gray-500">
                            Halaman dashboard pemesanan kendaraan akan dibahas &amp; dibuat pada
                            tahap berikutnya.
                        </p>
                    </div>
                </main>
            </div>
        </>
    );
}
