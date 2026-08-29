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
    {{-- Background gelap navy elegan (permintaan client, lihat docs/DECISION_LOG.md) - card
         konten TETAP terang/putih di atasnya, cuma latar belakangnya yang diganti gelap. --}}
    <body class="min-h-screen bg-gradient-to-br from-slate-950 via-blue-950 to-slate-900 flex items-center justify-center p-4 relative overflow-hidden">
        <div class="pointer-events-none absolute -top-24 -left-24 w-80 h-80 rounded-full bg-blue-500/20 blur-3xl" aria-hidden="true"></div>
        <div class="pointer-events-none absolute -bottom-32 -right-16 w-96 h-96 rounded-full bg-cyan-400/10 blur-3xl" aria-hidden="true"></div>
        <div class="pointer-events-none absolute top-1/4 right-1/3 w-72 h-72 rounded-full bg-blue-400/10 blur-3xl" aria-hidden="true"></div>

        <div class="relative w-full max-w-sm bg-white rounded-xl border border-slate-200 shadow-xl shadow-black/30 p-6 sm:p-8">
            {{ $slot }}
        </div>

        @livewireScripts
    </body>
</html>
