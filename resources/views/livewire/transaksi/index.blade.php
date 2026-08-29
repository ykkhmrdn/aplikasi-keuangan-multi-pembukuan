<div class="space-y-6">
    @include('livewire._pill-switcher', ['pembukuan' => $pembukuan, 'routeName' => 'transaksi.index'])

    @include('livewire._page-header', [
        'judul' => 'Transaksi '.$pembukuan->nama,
        'tombolLabel' => '+ Tambah',
        'tombolAction' => 'tambah',
    ])

    {{-- Form tambah/edit --}}
    @if ($showForm)
        <form wire:submit="simpan" class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm shadow-slate-900/5 space-y-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Tipe</label>
                <select
                    wire:model.live="tipe"
                    class="w-full rounded-xl border px-3 py-2.5 text-slate-900 transition-colors focus:outline-none focus:ring-2
                        {{ $errors->has('tipe') ? 'border-red-400 focus:border-red-500 focus:ring-red-500/10' : 'border-slate-200 focus:border-blue-400 focus:ring-blue-600/10' }}"
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
                    class="w-full rounded-xl border px-3 py-2.5 text-slate-900 transition-colors focus:outline-none focus:ring-2
                        {{ $errors->has('kategoriId') ? 'border-red-400 focus:border-red-500 focus:ring-red-500/10' : 'border-slate-200 focus:border-blue-400 focus:ring-blue-600/10' }}"
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
                @include('livewire._input-uang', ['field' => 'jumlah'])
                @error('jumlah') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Tanggal</label>
                <input
                    type="date"
                    wire:model="tanggal"
                    class="w-full rounded-xl border px-3 py-2.5 text-slate-900 transition-colors focus:outline-none focus:ring-2
                        {{ $errors->has('tanggal') ? 'border-red-400 focus:border-red-500 focus:ring-red-500/10' : 'border-slate-200 focus:border-blue-400 focus:ring-blue-600/10' }}"
                >
                @error('tanggal') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Keterangan (opsional)</label>
                <textarea
                    wire:model="keterangan"
                    rows="2"
                    class="w-full rounded-xl border px-3 py-2.5 text-slate-900 transition-colors focus:outline-none focus:ring-2
                        {{ $errors->has('keterangan') ? 'border-red-400 focus:border-red-500 focus:ring-red-500/10' : 'border-slate-200 focus:border-blue-400 focus:ring-blue-600/10' }}"
                ></textarea>
                @error('keterangan') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex gap-2 pt-1">
                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    wire:target="simpan"
                    class="rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-blue-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600/40 disabled:opacity-50 motion-safe:active:scale-[0.97]"
                >
                    Simpan
                </button>
                <button
                    type="button"
                    wire:click="batal"
                    class="rounded-lg border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700 transition-colors hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600/20 motion-safe:active:scale-[0.97]"
                >
                    Batal
                </button>
            </div>
        </form>
    @endif

    @include('livewire._search-bar', ['placeholder' => 'Cari keterangan atau nama kategori...'])

    {{-- Filter & urutan --}}
    <div class="flex flex-wrap gap-2">
        <select wire:model.live="filterKategori" class="rounded-xl border border-slate-200 bg-white px-2.5 py-2 text-sm text-slate-700 shadow-sm shadow-slate-900/5 transition-colors focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-600/10">
            <option value="semua">Semua kategori</option>
            @foreach ($kategoriSemua as $kategori)
                <option value="{{ $kategori->id }}">{{ $kategori->tipe->label() }} - {{ $kategori->nama }}</option>
            @endforeach
        </select>

        <input type="date" wire:model.live="filterDari" class="rounded-xl border border-slate-200 bg-white px-2.5 py-2 text-sm text-slate-700 shadow-sm shadow-slate-900/5 transition-colors focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-600/10" placeholder="Dari tanggal">
        <input type="date" wire:model.live="filterSampai" class="rounded-xl border border-slate-200 bg-white px-2.5 py-2 text-sm text-slate-700 shadow-sm shadow-slate-900/5 transition-colors focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-600/10" placeholder="Sampai tanggal">

        <select wire:model.live="sort" class="rounded-xl border border-slate-200 bg-white px-2.5 py-2 text-sm text-slate-700 shadow-sm shadow-slate-900/5 transition-colors focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-600/10">
            <option value="tanggal_terbaru">Tanggal terbaru</option>
            <option value="tanggal_terlama">Tanggal terlama</option>
            <option value="jumlah_terbesar">Jumlah terbesar</option>
            <option value="jumlah_terkecil">Jumlah terkecil</option>
        </select>
    </div>

    {{-- List transaksi --}}
    <div class="space-y-2">
        @forelse ($transaksiList as $transaksi)
            <div wire:key="transaksi-{{ $transaksi->id }}" class="rounded-xl border border-slate-200 bg-gradient-to-br from-white to-blue-50 p-4 shadow-sm shadow-slate-900/5">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-sm sm:text-base font-semibold text-slate-900 truncate">{{ $transaksi->kategori->nama }}</p>
                        <p class="text-xs text-slate-500 mt-0.5 truncate">
                            {{ $transaksi->tanggal->translatedFormat('d M Y') }}
                            @if ($transaksi->keterangan)
                                &middot; {{ $transaksi->keterangan }}
                            @endif
                        </p>
                    </div>

                    <div class="text-right shrink-0 max-w-[45%]">
                        <p class="font-bold tabular-nums break-words {{ $transaksi->tipe === \App\Enums\TipeTransaksi::Pemasukan ? 'text-emerald-700' : 'text-red-700' }}">
                            {{ $transaksi->tipe === \App\Enums\TipeTransaksi::Pemasukan ? '+' : '-' }}Rp{{ number_format($transaksi->jumlah, 0, ',', '.') }}
                        </p>
                        <div class="mt-1.5 flex justify-end items-center gap-2 text-sm">
                            <button
                                wire:click="edit({{ $transaksi->id }})"
                                class="rounded-xl border border-slate-200 bg-white px-3 py-1.5 font-semibold text-slate-700 transition-all duration-200 hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600/20 motion-safe:active:scale-[0.97]"
                            >
                                Edit
                            </button>
                            <button
                                wire:click="confirmHapus({{ $transaksi->id }})"
                                class="rounded-xl border border-red-200 bg-red-50 px-3 py-1.5 font-semibold text-red-700 transition-all duration-200 hover:bg-red-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-600/30 motion-safe:active:scale-[0.97]"
                            >
                                Hapus
                            </button>
                        </div>

                        @if ($confirmingDeleteId === $transaksi->id)
                            @include('livewire._modal-konfirmasi-hapus', [
                                'judul' => 'Hapus Transaksi',
                                'pesan' => 'Hapus transaksi "'.$transaksi->kategori->nama.'" sebesar Rp'.number_format($transaksi->jumlah, 0, ',', '.').'? Tindakan ini tidak bisa dibatalkan.',
                                'konfirmAction' => 'hapus('.$transaksi->id.')',
                                'batalAction' => 'batalHapus',
                            ])
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <p class="text-sm text-slate-400 text-center py-6">
                {{ $search !== '' ? 'Gak ada transaksi yang cocok dengan pencarian.' : 'Belum ada transaksi untuk filter ini.' }}
            </p>
        @endforelse
    </div>

    @if ($transaksiList->hasPages())
        <div class="pt-2">
            {{ $transaksiList->links() }}
        </div>
    @endif
</div>
