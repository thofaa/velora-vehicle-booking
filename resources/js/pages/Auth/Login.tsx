import { useForm, Link } from '@inertiajs/react';
import { useState, type FormEvent } from 'react';
import AuthInput from '@/components/AuthInput';
import AuthLayout from '@/components/AuthLayout';
import GoogleIcon from '@/components/GoogleIcon';

export default function Login() {
    const { data, setData, post, processing, errors, reset } = useForm({
        email: '',
        password: '',
        remember: false,
    });
    const [showForgot, setShowForgot] = useState(false);
    const forgot = useForm({ email: '' });

    function submit(e: FormEvent) {
        e.preventDefault();
        post('/login');
    }

    function submitForgot(e: FormEvent) {
        e.preventDefault();
        forgot.post('/forgot-password', {
            onFinish: () => reset('password'),
        });
    }

    return (
        <AuthLayout title="Masuk" subtitle="Masuk untuk mengelola pemesanan kendaraan">
            {showForgot ? (
                <form onSubmit={submitForgot} noValidate className="space-y-5">
                    <AuthInput
                        id="forgot-email"
                        label="Email"
                        type="email"
                        autoComplete="email"
                        placeholder="nama@contoh.com"
                        value={forgot.data.email}
                        onChange={(v) => forgot.setData('email', v)}
                        error={forgot.errors.email}
                        disabled={forgot.processing}
                    />
                    {forgot.recentlySuccessful && (
                        <p className="rounded-lg bg-emerald-500/20 px-3 py-2 text-sm text-emerald-100">
                            Jika email tersedia, link reset telah dikirim.
                        </p>
                    )}
                    <button
                        type="submit"
                        disabled={forgot.processing}
                        className="w-full rounded-xl bg-white py-3 text-sm font-semibold text-gray-900 transition-all duration-200 hover:bg-gray-100 active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-60"
                    >
                        {forgot.processing ? 'Mengirim...' : 'Kirim Link Reset'}
                    </button>
                    <button
                        type="button"
                        onClick={() => setShowForgot(false)}
                        className="w-full text-sm text-white/80 hover:text-white"
                    >
                        Kembali ke Masuk
                    </button>
                </form>
            ) : (
                <form onSubmit={submit} noValidate className="space-y-5">
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

                    <div className="space-y-1.5">
                        <div className="flex items-center justify-between">
                            <label htmlFor="password" className="text-sm font-medium text-white/90">
                                Kata Sandi
                            </label>
                            <button
                                type="button"
                                onClick={() => setShowForgot(true)}
                                className="text-xs font-medium text-white/80 underline-offset-2 hover:text-white hover:underline"
                            >
                                Lupa Kata Sandi?
                            </button>
                        </div>
                        <input
                            id="password"
                            type="password"
                            autoComplete="current-password"
                            value={data.password}
                            disabled={processing}
                            onChange={(e) => setData('password', e.target.value)}
                            placeholder="••••••••"
                            className={`w-full rounded-xl border bg-white/10 px-4 py-3 text-sm text-white placeholder:text-white/50 transition-all duration-200 outline-none backdrop-blur disabled:cursor-not-allowed disabled:opacity-60 ${
                                errors.password
                                    ? 'border-red-400 focus:border-red-400 focus:ring-4 focus:ring-red-400/25'
                                    : 'border-white/25 focus:border-white focus:bg-white/15 focus:ring-4 focus:ring-white/20'
                            }`}
                        />
                        {errors.password && <p className="text-xs text-red-300">{errors.password}</p>}
                    </div>

                    <label className="flex items-center gap-2 text-sm text-white/90">
                        <input
                            type="checkbox"
                            checked={data.remember}
                            disabled={processing}
                            onChange={(e) => setData('remember', e.target.checked)}
                            className="h-4 w-4 rounded border-white/30 bg-white/10 accent-white"
                        />
                        Ingat saya
                    </label>

                    <button
                        type="submit"
                        disabled={processing}
                        className="w-full rounded-xl bg-white py-3 text-sm font-semibold text-gray-900 shadow-lg transition-all duration-200 hover:bg-gray-100 hover:shadow-xl active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-60"
                    >
                        {processing ? 'Memproses...' : 'Masuk'}
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
                        Belum punya akun?{' '}
                        <Link
                            href="/register"
                            className="font-semibold text-white underline-offset-2 hover:underline"
                        >
                            Daftar di sini
                        </Link>
                    </p>
                </form>
            )}
        </AuthLayout>
    );
}
