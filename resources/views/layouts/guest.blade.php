<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'YMB-MET') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-[var(--surface-muted)]">
            <div class="text-center mb-6">
                <img src="{{ asset('images/yamaha-logo.jpg') }}" alt="Yamaha" class="h-12 mx-auto mb-3">
                <h1 class="text-sm font-semibold uppercase tracking-widest text-[var(--yamaha-black)]">Marketing Expense Tracker</h1>
            </div>
            <div class="w-full sm:max-w-md px-6 py-6 bg-white border border-[var(--yamaha-silver)] sm:rounded-sm">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
