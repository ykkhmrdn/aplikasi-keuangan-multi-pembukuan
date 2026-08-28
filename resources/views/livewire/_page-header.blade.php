{{--
    Header halaman: judul + tombol aksi opsional (disembunyikan otomatis kalau form
    lagi kebuka - baca $showForm dari scope komponen pemanggil, semua halaman yang
    pakai partial ini (Kategori/Transaksi/Transfer/Hutang-Piutang) sama-sama punya
    property $showForm).

    Variabel yang diharapkan dari pemanggil:
    $judul          - judul halaman, mis. "Kategori"
    $tombolLabel    - (opsional) label tombol, mis. "+ Tambah". Kalau gak diisi, tombol gak dirender.
    $tombolAction   - (opsional) nama method Livewire buat wire:click tombol, mis. "tambah"
--}}
<div class="flex items-center justify-between">
    <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900">{{ $judul }}</h1>
    @if (($tombolLabel ?? null) && ! ($showForm ?? false))
        <button
            wire:click="{{ $tombolAction }}"
            class="rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-blue-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600/40 motion-safe:active:scale-[0.97]"
        >
            {{ $tombolLabel }}
        </button>
    @endif
</div>
