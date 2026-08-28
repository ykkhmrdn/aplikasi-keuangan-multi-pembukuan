<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>{{ $title ?? config('app.name') }}</title>
        <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @livewireStyles
    </head>
    <body class="min-h-screen bg-slate-50 flex items-center justify-center p-4 relative overflow-hidden">
        <div class="pointer-events-none absolute -top-24 -left-24 w-80 h-80 rounded-full bg-blue-200/40 blur-3xl" aria-hidden="true"></div>
        <div class="pointer-events-none absolute -bottom-32 -right-16 w-96 h-96 rounded-full bg-blue-100/50 blur-3xl" aria-hidden="true"></div>

        <div class="relative w-full max-w-sm bg-white rounded-xl border border-slate-200 shadow-sm p-6 sm:p-8">
            {{ $slot }}
        </div>

        @livewireScripts
    </body>
</html>
