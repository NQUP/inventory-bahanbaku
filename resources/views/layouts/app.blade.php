<!DOCTYPE html>
<html lang="id" data-theme="cupcake" class="h-full">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') | PT. Dharma Karyatama Mulia</title>

    {{-- Styles & Scripts via Vite --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Favicon --}}
<link rel="icon" href="/images/logopt.png?v=1" type="image/png">
<link rel="shortcut icon" href="/images/logopt.png?v=1" type="image/png">
<link rel="apple-touch-icon" sizes="180x180" href="/images/logopt.png?v=1">
   
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>

<body class="h-full font-sans antialiased">

    {{-- Navbar with DaisyUI --}}
    <div class="navbar bg-primary text-white shadow-lg px-4">
        <div class="flex-1 flex items-center gap-2">
            <i class="fa fa-cubes text-2xl"></i>
            <a href="{{ route('dashboard') }}" class="text-lg font-bold tracking-wide">
                PT. Dharma Karyatama Mulia
            </a>
        </div>
        <div class="flex-none gap-4 items-center">
            @auth
                <div class="hidden sm:flex items-center gap-2 text-sm">
                    <i class="fa fa-user-circle text-xl"></i>
                    <span>
                        Hi, <span class="font-semibold">{{ auth()->user()->name }}</span>
                        ({{ ucfirst(auth()->user()->role) }})
                    </span>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="btn btn-sm btn-error text-white flex items-center gap-1">
                        <i class="fa fa-sign-out"></i> Logout
                    </button>
                </form>
            @endauth
        </div>
    </div>

    {{-- Main Content --}}
    <main class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @yield('content')
        </div>
    </main>

    {{-- Additional Scripts --}}
    @stack('scripts')

</body>
</html>
