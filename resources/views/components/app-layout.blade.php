<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>App Layout</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100 font-sans antialiased">
    @include('layouts.navigation')

    @if (isset($header))
    <header class="bg-white shadow">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            {{ $header }}
        </div>
    </header>
    @endif

    <main>
        {{ $slot }}
    </main>
</body>

</html>