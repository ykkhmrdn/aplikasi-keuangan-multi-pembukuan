<div class="space-y-6">
    {{-- Switcher pembukuan, warna pill aktif sesuai identitas pembukuan (konsisten sama Dashboard) --}}
    @php
        $accentPill = [
            'pribadi' => 'bg-indigo-600 text-white',
            'usaha' => 'bg-teal-600 text-white',
            'kantor' => 'bg-violet-600 text-white',
        ];
    @endphp
    <div class="flex gap-2">
        @foreach (\App\Enums\TipePembukuan::cases() as $tipePembukuan)
            <a
                href="{{ route('transaksi.index', $tipePembukuan->value) }}"
                class="rounded-full px-3 py-1.5 text-sm font-medium transition-colors
                    {{ $pembukuan->tipe === $tipePembukuan ? $accentPill[$tipePembukuan->value] : 'bg-white border border-slate-300 text-slate-600 hover:bg-slate-50' }}"
            >
                {{ $tipePembukuan->label() }}
            </a>
        @endforeach
    </div>

    <div class="flex items-center justify-between">
        <h1 class="text-xl sm:text-2xl font-semibold tracking-tight text-slate-900">Transaksi {{ $pembukuan->nama }}</h1>
        @unless ($showForm)
            <button
                wire:click="tambah"
                class="rounded-lg bg-slate-900 px-3 py-2 text-sm font-medium text-white transition-colors hover:bg-slate-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900/30"
            >
                + Tambah
            </button>
        @endunless
    </div>

    {{-- Form tambah/edit --}}
    @if ($showForm)
        <form wire:submit="simpan" class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm space-y-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Tipe</label>
                <select
                    wire:model.live="tipe"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-slate-900 transition-colors focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-900/10"
                >
                    @foreach ($tipeOptions as $option)
                        <option value="{{ $option->value }}">{{ $option->label() }}</option>
                    @endforeach
                </select>
                @error('tipe') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Kategori</label>
                <select
                    wire:model="kategoriId"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-slate-900 transition-colors focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-900/10"
                >
                    <option value="">Pilih kategori</option>
                    @foreach ($kategoriSemua->where('tipe', \App\Enums\TipeTransaksi::from($tipe)) as $kategori)
                        <option value="{{ $kategori->id }}">
                            {{ $kategori->nama }} @if (! $kategori->pembukuan_id) (Global) @endif
                        </option>
                    @endforeach
                </select>
                @error('kategoriId') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Jumlah</label>
                <input
                    type="number" step="0.01" min="0"
                    wire:model="jumlah"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-slate-900 transition-colors focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-900/10"
                >
                @error('jumlah') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Tanggal</label>
                <input
                    type="date"
                    wire:model="tanggal"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-slate-900 transition-colors focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-900/10"
                >
                @error('tanggal') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Keterangan (opsional)</label>
                <textarea
                    wire:model="keterangan"
                    rows="2"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-slate-900 transition-colors focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-900/10"
                ></textarea>
                @error('keterangan') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex gap-2 pt-1">
                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    wire:target="simpan"
                    class="rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-medium text-white transition-colors hover:bg-slate-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900/30 disabled:opacity-50"
                >
                    Simpan
                </button>
                <button
                    type="button"
                    wire:click="batal"
                    class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 transition-colors hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900/20"
                >
                    Batal
                </button>
            </div>
        </form>
    @endif

    {{-- Filter --}}
    <div class="flex flex-wrap gap-2">
        <select wire:model.live="filterKategori" class="rounded-lg border border-slate-300 px-2.5 py-2 text-sm text-slate-700 transition-colors focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-900/10">
            <option value="semua">Semua kategori</option>
            @foreach ($kategoriSemua as $kategori)
                <option value="{{ $kategori->id }}">{{ $kategori->tipe->label() }} - {{ $kategori->nama }}</option>
            @endforeach
        </select>

        <input type="date" wire:model.live="filterDari" class="rounded-lg border border-slate-300 px-2.5 py-2 text-sm text-slate-700 transition-colors focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-900/10" placeholder="Dari tanggal">
        <input type="date" wire:model.live="filterSampai" class="rounded-lg border border-slate-300 px-2.5 py-2 text-sm text-slate-700 transition-colors focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-900/10" placeholder="Sampai tanggal">
    </div>

    {{-- List transaksi --}}
    <div class="space-y-2">
        @forelse ($transaksiList as $transaksi)
            <div wire:key="transaksi-{{ $transaksi->id }}" class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-sm sm:text-base font-medium text-slate-900 truncate">{{ $transaksi->kategori->nama }}</p>
                        <p class="text-xs text-slate-500 mt-0.5 truncate">
                            {{ $transaksi->tanggal->translatedFormat('d M Y') }}
                            @if ($transaksi->keterangan)
                                &middot; {{ $transaksi->keterangan }}
                            @endif
                        </p>
                    </div>

                    <div class="text-right shrink-0 max-w-[45%]">
                        <p class="font-semibold tabular-nums break-words {{ $transaksi->tipe === \App\Enums\TipeTransaksi::Pemasukan ? 'text-emerald-700' : 'text-red-700' }}">
                            {{ $transaksi->tipe === \App\Enums\TipeTransaksi::Pemasukan ? '+' : '-' }}Rp{{ number_format($transaksi->jumlah, 0, ',', '.') }}
                        </p>
                        <div class="mt-1 flex justify-end items-center gap-3 text-sm">
                            <button wire:click="edit({{ $transaksi->id }})" class="text-slate-500 hover:text-slate-800 focus-visible:outline-none focus-visible:underline">Edit</button>

                            @if ($confirmingDeleteId === $transaksi->id)
                                <span class="inline-flex items-center gap-2 rounded-lg bg-red-50 px-2 py-1">
                                    <span class="text-xs text-red-700">Yakin?</span>
                                    <button wire:click="hapus({{ $transaksi->id }})" class="text-xs font-semibold text-red-700 hover:text-red-900 focus-visible:outline-none focus-visible:underline">Ya</button>
                                    <button wire:click="batalHapus" class="text-xs text-slate-500 hover:text-slate-800 focus-visible:outline-none focus-visible:underline">Batal</button>
                                </span>
                            @else
                                <button wire:click="confirmHapus({{ $transaksi->id }})" class="text-red-600 hover:text-red-800 focus-visible:outline-none focus-visible:underline">Hapus</button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <p class="text-sm text-slate-500 text-center py-6">Belum ada transaksi untuk filter ini.</p>
        @endforelse
    </div>
</div>
