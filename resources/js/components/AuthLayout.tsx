import { Link } from '@inertiajs/react';
import { type ReactNode } from 'react';

const BACKGROUND_IMAGE =
    'https://i.pinimg.com/736x/83/0e/b1/830eb1fd0cac71a9c58b9f98b2cc1c53.jpg';

export default function AuthLayout({
    title,
    subtitle,
    children,
}: {
    title: string;
    subtitle?: string;
    children: ReactNode;
}) {
    return (
        <div className="relative flex min-h-screen flex-col overflow-hidden">
            <div
                className="absolute inset-0 bg-cover bg-center"
                style={{ backgroundImage: `url(${BACKGROUND_IMAGE})` }}
            />
            <div className="absolute inset-0 bg-black/40" />

            <header className="animate-slide-down relative z-10 flex items-center justify-between px-5 py-5 sm:px-8">
                <Link
                    href="/"
                    className="text-lg font-semibold tracking-wide text-white drop-shadow sm:text-xl"
                >
                    Vehicle Booking
                </Link>
                <nav className="flex items-center gap-1 rounded-full bg-white/10 px-2 py-1 backdrop-blur">
                    {[
                        { href: '/login', label: 'Masuk', active: title === 'Masuk' },
                        { href: '/register', label: 'Daftar', active: title === 'Daftar' },
                    ].map((item) => (
                        <Link
                            key={item.href}
                            href={item.href}
                            className={`rounded-full px-4 py-1.5 text-sm font-medium transition-colors duration-300 ${
                                item.active
                                    ? 'bg-white text-gray-900'
                                    : 'text-white hover:bg-white/20'
                            }`}
                        >
                            {item.label}
                        </Link>
                    ))}
                </nav>
            </header>

            <main className="relative z-10 flex flex-1 items-center justify-center px-4 py-10">
                <div className="animate-slide-down-soft w-full max-w-md [animation-delay:250ms]">
                    <div className="rounded-3xl border border-white/20 bg-white/10 p-8 shadow-2xl backdrop-blur-md sm:p-10">
                        <h1 className="text-center text-3xl font-bold text-white">{title}</h1>
                        {subtitle && (
                            <p className="mt-2 text-center text-sm text-white/80">{subtitle}</p>
                        )}
                        <div className="mt-8">{children}</div>
                    </div>
                </div>
            </main>
        </div>
    );
}
