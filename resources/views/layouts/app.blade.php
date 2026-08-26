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
        @php
            $navItems = [
                ['route' => 'dashboard', 'params' => [], 'active' => request()->routeIs('dashboard'), 'label' => 'Dashboard'],
                ['route' => 'transaksi.index', 'params' => [\App\Enums\TipePembukuan::Pribadi->value], 'active' => request()->routeIs('transaksi.*'), 'label' => 'Transaksi'],
                ['route' => 'transfer.index', 'params' => [], 'active' => request()->routeIs('transfer.*'), 'label' => 'Transfer'],
                ['route' => 'hutang-piutang.index', 'params' => [\App\Enums\TipePembukuan::Pribadi->value], 'active' => request()->routeIs('hutang-piutang.*'), 'label' => 'Hutang'],
                ['route' => 'kategori.index', 'params' => [], 'active' => request()->routeIs('kategori.*'), 'label' => 'Kategori'],
            ];
        @endphp

        {{-- Header: brand + logout selalu, link menu cuma tampil di desktop (mobile pakai tab bar bawah) --}}
        <header class="bg-white border-b border-slate-200 sticky top-0 z-30">
            <div class="max-w-3xl mx-auto px-4 h-14 flex items-center justify-between gap-3">
                <span class="font-semibold text-slate-900 shrink-0">{{ config('app.name') }}</span>

                <nav class="hidden sm:flex items-center gap-1 text-sm">
                    @foreach ($navItems as $item)
                        <a
                            href="{{ route($item['route'], $item['params']) }}"
                            class="rounded-full px-3 py-1.5 font-medium transition-colors
                                {{ $item['active'] ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100' }}"
                        >
                            {{ $item['label'] === 'Hutang' ? 'Hutang-Piutang' : $item['label'] }}
                        </a>
                    @endforeach
                </nav>

                <form method="POST" action="{{ route('logout') }}" class="shrink-0">
                    @csrf
                    <button type="submit" class="text-sm text-slate-500 hover:text-slate-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900/20 rounded">
                        Keluar
                    </button>
                </form>
            </div>
        </header>

        <main class="max-w-3xl mx-auto px-4 py-6 pb-24 sm:pb-6">
            {{ $slot }}
        </main>

        {{-- Tab bar bawah: mobile saja, 5 slot rata jadi gak pernah overflow/butuh scroll --}}
        <nav class="sm:hidden fixed inset-x-0 bottom-0 z-40 bg-white border-t border-slate-200 pb-[env(safe-area-inset-bottom)]">
            <div class="grid grid-cols-5">
                @foreach ($navItems as $item)
                    <a
                        href="{{ route($item['route'], $item['params']) }}"
                        class="flex flex-col items-center justify-center gap-0.5 py-2.5 text-[11px] font-medium focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900/20 rounded-lg
                            {{ $item['active'] ? 'text-slate-900' : 'text-slate-400' }}"
                    >
                        <span class="w-6 h-6">
                            @switch($item['label'])
                                @case('Dashboard')
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" class="w-full h-full">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 10.5L12 4l9 6.5M5 9.5V19a1 1 0 001 1h4v-5h4v5h4a1 1 0 001-1V9.5" />
                                    </svg>
                                    @break
                                @case('Transaksi')
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" class="w-full h-full">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 6h12M8 12h12M8 18h12M4 6h.01M4 12h.01M4 18h.01" />
                                    </svg>
                                    @break
                                @case('Transfer')
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" class="w-full h-full">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 8h11m0 0l-3.5-3.5M18 8l-3.5 3.5M17 16H6m0 0l3.5-3.5M6 16l3.5 3.5" />
                                    </svg>
                                    @break
                                @case('Hutang')
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" class="w-full h-full">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 12a8 8 0 0114-5.3M20 12a8 8 0 01-14 5.3M15 4.5h3.5V8M9 19.5H5.5V16" />
                                    </svg>
                                    @break
                                @case('Kategori')
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" class="w-full h-full">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 11.5V4a1 1 0 011-1h7.5L21 12.5 12.5 21 3 11.5z" />
                                        <circle cx="7.5" cy="7.5" r="1.1" fill="currentColor" stroke="none" />
                                    </svg>
                                    @break
                            @endswitch
                        </span>
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </div>
        </nav>

        @livewireScripts
    </body>
</html>
