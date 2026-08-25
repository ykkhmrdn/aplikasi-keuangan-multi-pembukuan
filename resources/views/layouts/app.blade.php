<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>{{ $title ?? config('app.name') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @livewireStyles
    </head>
    <body class="min-h-screen bg-slate-50">
        <nav class="bg-white border-b border-slate-200">
            <div class="max-w-3xl mx-auto px-4 h-14 flex items-center justify-between">
                <span class="font-semibold text-slate-900">{{ config('app.name') }}</span>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-sm text-slate-500 hover:text-slate-800">
                        Keluar
                    </button>
                </form>
            </div>
        </nav>

        <main class="max-w-3xl mx-auto px-4 py-6">
            {{ $slot }}
        </main>

        @livewireScripts
    </body>
</html>
