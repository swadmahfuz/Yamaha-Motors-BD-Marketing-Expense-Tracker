<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'YMB-MET') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-[var(--surface-muted)]">
            @include('layouts.navigation')

            @if (isset($header))
                <header class="bg-white border-b border-[var(--yamaha-silver)]">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            @if (session('success'))
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
                    <div class="card border-l-4 border-l-green-600 text-sm text-green-800">{{ session('success') }}</div>
                </div>
            @endif

            @if (session('error'))
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
                    <div class="card border-l-4 border-l-[var(--yamaha-red)] text-sm text-[var(--yamaha-red)]">{{ session('error') }}</div>
                </div>
            @endif

            <main class="py-8">
                {{ $slot }}
            </main>
        </div>
        @livewireScripts
    </body>
</html>
