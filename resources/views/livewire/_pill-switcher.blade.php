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
        'pribadi' => 'bg-blue-600 text-white',
        'usaha' => 'bg-teal-600 text-white',
        'kantor' => 'bg-violet-600 text-white',
    ];
@endphp
<div class="flex gap-2">
    @foreach (\App\Enums\TipePembukuan::cases() as $tipePembukuan)
        <a
            href="{{ route($routeName, $tipePembukuan->value) }}"
            class="rounded-full px-4 py-2 text-sm font-semibold transition-colors
                {{ $pembukuan->tipe === $tipePembukuan ? $accentPill[$tipePembukuan->value] : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' }}"
        >
            {{ $tipePembukuan->label() }}
        </a>
    @endforeach
</div>
