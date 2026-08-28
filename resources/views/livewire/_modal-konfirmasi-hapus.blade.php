{{--
    Modal konfirmasi hapus generik, dipakai di Kategori & Transaksi (ganti dari chip
    inline "Yakin? Ya/Batal" - chip kekecilan buat ditekan di mobile, target sentuhnya
    di bawah standar 44x44px). Bukan confirm() bawaan browser, tetap custom sesuai
    aturan kerja poin 5.

    Variabel yang diharapkan dari pemanggil:
    $judul          - judul modal, mis. "Hapus Kategori"
    $pesan          - kalimat konfirmasi, mis. 'Hapus kategori "Gaji"? Tindakan ini tidak bisa dibatalkan.'
    $konfirmAction  - nama method Livewire buat tombol "Ya, Hapus", mis. "hapus(3)"
    $batalAction    - nama method Livewire buat tombol "Batal" & klik backdrop, mis. "batalHapus"
--}}
<div
    class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 px-4"
    wire:click.self="{{ $batalAction }}"
>
    <div class="w-full max-w-sm rounded-3xl bg-white p-5 shadow-xl shadow-slate-900/10">
        <h3 class="text-base font-bold text-slate-900">{{ $judul }}</h3>
        <p class="mt-1.5 text-sm text-slate-600">{{ $pesan }}</p>
        <div class="mt-5 flex gap-2">
            <button
                type="button"
                wire:click="{{ $konfirmAction }}"
                class="min-h-11 flex-1 rounded-xl border border-red-200 bg-red-50 px-4 text-sm font-semibold text-red-700 transition-all duration-200 hover:bg-red-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-600/30 motion-safe:active:scale-[0.97]"
            >
                Ya, Hapus
            </button>
            <button
                type="button"
                wire:click="{{ $batalAction }}"
                class="min-h-11 flex-1 rounded-xl border border-slate-200 px-4 text-sm font-semibold text-slate-700 transition-all duration-200 hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-600/20 motion-safe:active:scale-[0.97]"
            >
                Batal
            </button>
        </div>
    </div>
</div>
