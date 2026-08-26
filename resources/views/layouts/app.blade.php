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
            <div class="max-w-3xl mx-auto px-4 h-14 flex items-center justify-between gap-4">
                <div class="flex items-center gap-4 min-w-0">
                    <span class="font-semibold text-slate-900 shrink-0">{{ config('app.name') }}</span>

                    <div class="flex gap-3 text-sm">
                        <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'text-slate-900 font-medium' : 'text-slate-500 hover:text-slate-800' }}">
                            Dashboard
                        </a>
                        <a href="{{ route('transaksi.index', \App\Enums\TipePembukuan::Pribadi->value) }}" class="{{ request()->routeIs('transaksi.*') ? 'text-slate-900 font-medium' : 'text-slate-500 hover:text-slate-800' }}">
                            Transaksi
                        </a>
                        <a href="{{ route('transfer.index') }}" class="{{ request()->routeIs('transfer.*') ? 'text-slate-900 font-medium' : 'text-slate-500 hover:text-slate-800' }}">
                            Transfer
                        </a>
                        <a href="{{ route('hutang-piutang.index', \App\Enums\TipePembukuan::Pribadi->value) }}" class="{{ request()->routeIs('hutang-piutang.*') ? 'text-slate-900 font-medium' : 'text-slate-500 hover:text-slate-800' }}">
                            Hutang-Piutang
                        </a>
                        <a href="{{ route('kategori.index') }}" class="{{ request()->routeIs('kategori.*') ? 'text-slate-900 font-medium' : 'text-slate-500 hover:text-slate-800' }}">
                            Kategori
                        </a>
                    </div>
                </div>

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
