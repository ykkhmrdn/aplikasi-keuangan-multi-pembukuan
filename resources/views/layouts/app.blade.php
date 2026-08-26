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
            <div class="max-w-3xl mx-auto px-4">
                <div class="h-14 flex items-center justify-between gap-3">
                    <span class="font-semibold text-slate-900 shrink-0">{{ config('app.name') }}</span>

                    <form method="POST" action="{{ route('logout') }}" class="shrink-0">
                        @csrf
                        <button type="submit" class="text-sm text-slate-500 hover:text-slate-800">
                            Keluar
                        </button>
                    </form>
                </div>

                <div class="flex gap-4 text-sm overflow-x-auto pb-2 -mx-4 px-4 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                    <a href="{{ route('dashboard') }}" class="shrink-0 whitespace-nowrap {{ request()->routeIs('dashboard') ? 'text-slate-900 font-medium' : 'text-slate-500 hover:text-slate-800' }}">
                        Dashboard
                    </a>
                    <a href="{{ route('transaksi.index', \App\Enums\TipePembukuan::Pribadi->value) }}" class="shrink-0 whitespace-nowrap {{ request()->routeIs('transaksi.*') ? 'text-slate-900 font-medium' : 'text-slate-500 hover:text-slate-800' }}">
                        Transaksi
                    </a>
                    <a href="{{ route('transfer.index') }}" class="shrink-0 whitespace-nowrap {{ request()->routeIs('transfer.*') ? 'text-slate-900 font-medium' : 'text-slate-500 hover:text-slate-800' }}">
                        Transfer
                    </a>
                    <a href="{{ route('hutang-piutang.index', \App\Enums\TipePembukuan::Pribadi->value) }}" class="shrink-0 whitespace-nowrap {{ request()->routeIs('hutang-piutang.*') ? 'text-slate-900 font-medium' : 'text-slate-500 hover:text-slate-800' }}">
                        Hutang-Piutang
                    </a>
                    <a href="{{ route('kategori.index') }}" class="shrink-0 whitespace-nowrap {{ request()->routeIs('kategori.*') ? 'text-slate-900 font-medium' : 'text-slate-500 hover:text-slate-800' }}">
                        Kategori
                    </a>
                </div>
            </div>
        </nav>

        <main class="max-w-3xl mx-auto px-4 py-6">
            {{ $slot }}
        </main>

        @livewireScripts
    </body>
</html>
