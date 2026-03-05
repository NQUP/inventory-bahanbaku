@extends('layouts.guest')

@section('content')
<div class="mx-auto w-full max-w-md space-y-6 slide-up">
    <div class="text-center">
        <img src="{{ asset('images/logopt.png') }}" alt="Logo" class="mx-auto mb-3 h-16 w-16 object-contain">
        <h1 class="text-2xl font-extrabold text-slate-900">Masuk ke Sistem</h1>
        <p class="mt-1 text-sm text-slate-500">Gunakan email dan password akun Anda untuk melanjutkan.</p>
    </div>

    <x-auth-session-status class="rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-4 rounded-2xl border border-slate-200 p-5 shadow-sm">
        @csrf

        <div class="space-y-1">
            <label for="email" class="text-sm font-semibold text-slate-700">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" class="input input-bordered w-full" placeholder="contoh: admin@gmail.com" />
            <x-input-error :messages="$errors->get('email')" class="text-xs text-error" />
        </div>

        <div class="space-y-1">
            <label for="password" class="text-sm font-semibold text-slate-700">Password</label>
            <input id="password" type="password" name="password" required autocomplete="current-password" class="input input-bordered w-full" placeholder="Masukkan password" />
            <x-input-error :messages="$errors->get('password')" class="text-xs text-error" />
        </div>

        <div class="flex items-center justify-between">
            <label class="inline-flex items-center gap-2 text-sm text-slate-600">
                <input type="checkbox" name="remember" class="checkbox checkbox-sm checkbox-primary" />
                Ingat saya
            </label>

            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="text-sm font-semibold text-primary hover:underline">Lupa password?</a>
            @endif
        </div>

        <button type="submit" class="btn btn-primary w-full">Masuk</button>
    </form>

    <div class="rounded-xl border border-amber-200 bg-amber-50 p-3 text-xs text-amber-800">
        <p class="font-semibold">Akun Demo Cepat</p>
        <p>admin@gmail.com / manager@gmail.com / gudang@gmail.com / supplier@gmail.com / pemesan@gmail.com</p>
        <p>Password: <span class="font-semibold">12345</span></p>
    </div>
</div>
@endsection
