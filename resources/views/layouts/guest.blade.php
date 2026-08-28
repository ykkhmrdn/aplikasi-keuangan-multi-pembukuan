<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>{{ $title ?? config('app.name') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @livewireStyles
    </head>
    {{-- Background gradasi lembut (slate->indigo->violet) + blob dekoratif blur besar,
         opacity rendah biar gak ganggu keterbacaan form. Signature "mobile banking"
         yang diminta client, dipakai di titik masuk aplikasi. --}}
    <body class="min-h-screen bg-gradient-to-br from-slate-50 via-indigo-50 to-violet-50 flex items-center justify-center p-4 relative overflow-hidden">
        <div class="pointer-events-none absolute -top-24 -left-24 w-72 h-72 rounded-full bg-indigo-300/30 blur-3xl" aria-hidden="true"></div>
        <div class="pointer-events-none absolute -bottom-32 -right-16 w-80 h-80 rounded-full bg-violet-300/30 blur-3xl" aria-hidden="true"></div>

        <div class="relative w-full max-w-sm bg-white rounded-3xl border border-slate-200 shadow-xl shadow-indigo-500/10 p-6 sm:p-8">
            {{ $slot }}
        </div>

        @livewireScripts
    </body>
</html>
