@props([
    'title',
    'value',
    'color' => 'blue', // opsi: blue, yellow, green, red, sky, gray
    'text' => 'white', // opsi: white, black
])

@php
    // Warna background berdasarkan input
    $bgClass = match ($color) {
        'blue' => 'bg-blue-500',
        'yellow' => 'bg-yellow-400',
        'green' => 'bg-green-500',
        'red' => 'bg-red-500',
        'sky' => 'bg-sky-500',
        'gray' => 'bg-gray-500',
        default => 'bg-gray-300',
    };

    // Warna teks berdasarkan input
    $textClass = match ($text) {
        'black' => 'text-black',
        'white' => 'text-white',
        default => 'text-white',
    };
@endphp

<div class="rounded-xl shadow-md p-4 {{ $bgClass }} {{ $textClass }}">
    <div class="text-sm font-semibold tracking-wide">{{ $title }}</div>
    <div class="text-3xl font-bold mt-1">{{ $value }}</div>
</div>
