<!DOCTYPE html>
<html lang="id" data-theme="light" class="h-full">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') | PT. Dharma Karyatama Mulia</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="icon" href="/images/logopt.png?v=1" type="image/png">
    <link rel="shortcut icon" href="/images/logopt.png?v=1" type="image/png">
    <link rel="apple-touch-icon" sizes="180x180" href="/images/logopt.png?v=1">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body class="h-full antialiased">
    <header class="app-nav sticky top-0 z-40">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 sm:px-6 lg:px-8">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                <div class="grid h-10 w-10 place-items-center rounded-xl bg-primary/15 text-primary">
                    <i class="fa fa-cubes text-lg"></i>
                </div>
                <div>
                    <p class="text-sm font-semibold leading-tight text-slate-900">PT. Dharma Karyatama Mulia</p>
                    <p class="text-xs text-slate-500 leading-tight">Inventory & Procurement Suite</p>
                </div>
            </a>

            @auth
                @php
                    $role = auth()->user()->getRoleNames()->first() ?? 'user';
                @endphp
                <div class="flex items-center gap-3">
                    @if ($role === 'admin')
                        <div class="hidden items-center gap-2 md:flex">
                            <a href="{{ route('admin.dashboard') }}" class="btn btn-sm btn-ghost">Admin</a>
                            <a href="{{ route('bahanbaku.index') }}" class="btn btn-sm btn-primary text-white">Bahan Baku</a>
                        </div>
                    @endif
                    <div class="hidden items-center gap-2 rounded-xl bg-slate-100 px-3 py-2 sm:flex">
                        <i class="fa fa-user-circle text-slate-500"></i>
                        <div class="text-xs leading-tight">
                            <p class="font-semibold text-slate-800">{{ auth()->user()->name }}</p>
                            <p class="text-slate-500">{{ strtoupper($role) }}</p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-error text-white">
                            <i class="fa fa-sign-out"></i>
                            <span class="hidden sm:inline">Logout</span>
                        </button>
                    </form>
                </div>
            @endauth
        </div>
    </header>

    <main class="py-6 sm:py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            @yield('content')
        </div>
    </main>

    @stack('scripts')
</body>

</html>
