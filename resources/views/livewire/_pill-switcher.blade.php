{{--
    Pill switcher Pribadi/Usaha/Kantor, dipakai di Transaksi & Hutang-Piutang (sebelumnya
    duplikat identik di 2 tempat). Warna pill aktif sesuai identitas pembukuan, konsisten
    sama Dashboard.

    Variabel yang diharapkan dari pemanggil:
    $pembukuan  - model Pembukuan yang lagi dibuka
    $routeName  - nama route, mis. "transaksi.index" atau "hutang-piutang.index"
--}}
@php
    $accentPill = [
        'pribadi' => 'bg-gradient-to-r from-indigo-600 to-indigo-500 text-white shadow-md shadow-indigo-600/25',
        'usaha' => 'bg-gradient-to-r from-teal-600 to-teal-500 text-white shadow-md shadow-teal-600/25',
        'kantor' => 'bg-gradient-to-r from-violet-600 to-violet-500 text-white shadow-md shadow-violet-600/25',
    ];
@endphp
<div class="flex gap-2">
    @foreach (\App\Enums\TipePembukuan::cases() as $tipePembukuan)
        <a
            href="{{ route($routeName, $tipePembukuan->value) }}"
            class="rounded-full px-4 py-2 text-sm font-semibold transition-all duration-200
                {{ $pembukuan->tipe === $tipePembukuan ? $accentPill[$tipePembukuan->value] : 'bg-white border border-slate-200 text-slate-600 shadow-sm shadow-slate-900/5 hover:bg-slate-50' }}"
        >
            {{ $tipePembukuan->label() }}
        </a>
    @endforeach
</div>
