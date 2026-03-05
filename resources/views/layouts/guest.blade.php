<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'PT. Dharma Karyatama Mulia') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>
<body class="antialiased">
    <div class="relative min-h-screen px-4 py-10 sm:px-6 lg:px-8">
        <div class="mx-auto flex min-h-[calc(100vh-5rem)] max-w-5xl items-center justify-center">
            <div class="auth-shell grid w-full overflow-hidden lg:grid-cols-2">
                <aside class="hidden bg-slate-800 p-10 text-white lg:block">
                    <div class="space-y-6">
                        <div class="inline-flex items-center gap-3 rounded-xl bg-white/10 px-4 py-2">
                            <i class="fa fa-cubes"></i>
                            <span class="text-sm font-semibold">PT. Dharma Karyatama Mulia</span>
                        </div>
                        <h1 class="text-3xl font-extrabold leading-tight">Platform Operasional untuk Pemesanan dan Persediaan</h1>
                        <p class="text-sm text-slate-300">Satu tempat untuk admin, manager, gudang, supplier, dan pemesan dengan alur kerja yang konsisten.</p>
                    </div>
                </aside>

                <main class="bg-white p-6 sm:p-10">
                    @yield('content')
                </main>
            </div>
        </div>
    </div>
</body>
</html>

