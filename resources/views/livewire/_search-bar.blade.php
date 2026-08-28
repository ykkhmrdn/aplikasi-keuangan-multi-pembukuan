{{--
    Search bar dengan ikon, dipakai di Kategori/Transaksi/Transfer/Hutang-Piutang -
    semuanya bind ke property Livewire "search" yang sama, jadi nama field di-hardcode
    di sini (bukan parameter) biar simpel.

    Variabel yang diharapkan dari pemanggil:
    $placeholder - teks placeholder, mis. "Cari nama kategori..."
--}}
<div class="relative">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400">
        <circle cx="11" cy="11" r="7" />
        <path stroke-linecap="round" d="M21 21l-4.3-4.3" />
    </svg>
    <input
        type="text"
        wire:model.live.debounce.400ms="search"
        placeholder="{{ $placeholder }}"
        class="w-full rounded-lg border border-slate-200 bg-white pl-10 pr-3 py-2.5 text-sm text-slate-700 transition-colors focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-600/10"
    >
</div>
