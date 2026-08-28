import { usePage } from '@inertiajs/react';
import Header from '@/components/Header';
import type {ReactNode} from 'react';

export default function AppLayout({
    title,
    children,
}: {
    title: string;
    children: ReactNode;
}) {
    const { flash } = usePage().props as {
        flash?: { success?: string; error?: string };
    };

    return (
        <div className="min-h-screen bg-gray-50">
            <Header />

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
