import { useForm, Link } from '@inertiajs/react';
import { type FormEvent } from 'react';
import AuthInput from '@/components/AuthInput';
import AuthLayout from '@/components/AuthLayout';
import GoogleIcon from '@/components/GoogleIcon';

export default function Register() {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
    });

    function submit(e: FormEvent) {
        e.preventDefault();
        post('/register');
    }

    return (
        <AuthLayout title="Daftar" subtitle="Buat akun untuk mulai menggunakan layanan">
            <form onSubmit={submit} noValidate className="space-y-5">
                <AuthInput
                    id="name"
                    label="Nama Lengkap"
                    autoComplete="name"
                    placeholder="Nama Anda"
                    value={data.name}
                    onChange={(v) => setData('name', v)}
                    error={errors.name}
                    disabled={processing}
                />
                <AuthInput
                    id="email"
                    label="Email"
                    type="email"
                    autoComplete="email"
                    placeholder="nama@contoh.com"
                    value={data.email}
                    onChange={(v) => setData('email', v)}
                    error={errors.email}
                    disabled={processing}
                />
                <AuthInput
                    id="password"
                    label="Kata Sandi"
                    type="password"
                    autoComplete="new-password"
                    placeholder="••••••••"
                    value={data.password}
                    onChange={(v) => setData('password', v)}
                    error={errors.password}
                    disabled={processing}
                />
                <AuthInput
                    id="password_confirmation"
                    label="Konfirmasi Kata Sandi"
                    type="password"
                    autoComplete="new-password"
                    placeholder="••••••••"
                    value={data.password_confirmation}
                    onChange={(v) => setData('password_confirmation', v)}
                    error={errors.password_confirmation}
                    disabled={processing}
                />

                <button
                    type="submit"
                    disabled={processing}
                    className="w-full rounded-xl bg-white py-3 text-sm font-semibold text-gray-900 shadow-lg transition-all duration-200 hover:bg-gray-100 hover:shadow-xl active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-60"
                >
                    {processing ? 'Memproses...' : 'Daftar'}
                </button>

                <div className="flex items-center gap-3">
                    <span className="h-px flex-1 bg-white/20" />
                    <span className="text-xs text-white/70">atau</span>
                    <span className="h-px flex-1 bg-white/20" />
                </div>

                <button
                    type="button"
                    className="flex w-full items-center justify-center gap-3 rounded-xl border border-white/25 bg-white/10 py-3 text-sm font-medium text-white backdrop-blur transition-all duration-200 hover:bg-white/20 active:scale-[0.98]"
                >
                    <GoogleIcon className="h-5 w-5" />
                    Lanjut dengan Google
                </button>

                <p className="pt-1 text-center text-sm text-white/80">
                    Sudah punya akun?{' '}
                    <Link
                        href="/login"
                        className="font-semibold text-white underline-offset-2 hover:underline"
                    >
                        Masuk di sini
                    </Link>
                </p>
            </form>
        </AuthLayout>
    );
}
