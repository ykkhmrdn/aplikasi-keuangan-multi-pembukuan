<div class="space-y-6">
    @include('livewire._page-header', ['judul' => 'Kategori', 'tombolLabel' => '+ Tambah', 'tombolAction' => 'tambah'])

    @if ($deleteErrorMessage)
        <div class="rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
            {{ $deleteErrorMessage }}
        </div>
    @endif

    {{-- Form tambah/edit --}}
    @if ($showForm)
        <form wire:submit="simpan" class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm shadow-slate-900/5 space-y-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Nama</label>
                <input
                    type="text"
                    wire:model="nama"
                    autofocus
                    class="w-full rounded-xl border px-3 py-2.5 text-slate-900 transition-colors focus:outline-none focus:ring-2
                        {{ $errors->has('nama') ? 'border-red-400 focus:border-red-500 focus:ring-red-500/10' : 'border-slate-200 focus:border-blue-400 focus:ring-blue-600/10' }}"
                >
                @error('nama') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Tipe</label>
                <select
                    wire:model="tipe"
                    @if ($editingLocked) disabled @endif
                    class="w-full rounded-xl border px-3 py-2.5 text-slate-900 transition-colors focus:outline-none focus:ring-2 disabled:bg-slate-100 disabled:text-slate-500
                        {{ $errors->has('tipe') ? 'border-red-400 focus:border-red-500 focus:ring-red-500/10' : 'border-slate-200 focus:border-blue-400 focus:ring-blue-600/10' }}"
                >
                    @foreach ($tipeOptions as $option)
                        <option value="{{ $option->value }}">{{ $option->label() }}</option>
                    @endforeach
                </select>
                @if ($editingLocked)
                    <p class="mt-1.5 text-xs text-slate-500">Tipe tidak bisa diubah karena kategori sudah dipakai di transaksi.</p>
                @endif
                @error('tipe') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Pembukuan</label>
                <select
                    wire:model="pembukuanId"
                    class="w-full rounded-xl border px-3 py-2.5 text-slate-900 transition-colors focus:outline-none focus:ring-2
                        {{ $errors->has('pembukuanId') ? 'border-red-400 focus:border-red-500 focus:ring-red-500/10' : 'border-slate-200 focus:border-blue-400 focus:ring-blue-600/10' }}"
                >
                    <option value="global">Global (semua pembukuan)</option>
                    @foreach ($pembukuanList as $pembukuan)
                        <option value="{{ $pembukuan->id }}">{{ $pembukuan->nama }}</option>
                    @endforeach
                </select>
                @error('pembukuanId') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
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

    @include('livewire._search-bar', ['placeholder' => 'Cari nama kategori...'])

    {{-- Filter & urutan --}}
    <div class="flex flex-wrap gap-2">
        <select wire:model.live="filterTipe" class="rounded-xl border border-slate-200 bg-white px-2.5 py-2 text-sm text-slate-700 shadow-sm shadow-slate-900/5 transition-colors focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-600/10">
            <option value="semua">Semua tipe</option>
            @foreach ($tipeOptions as $option)
                <option value="{{ $option->value }}">{{ $option->label() }}</option>
            @endforeach
        </select>

        <select wire:model.live="filterPembukuan" class="rounded-xl border border-slate-200 bg-white px-2.5 py-2 text-sm text-slate-700 shadow-sm shadow-slate-900/5 transition-colors focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-600/10">
            <option value="semua">Semua pembukuan</option>
            <option value="global">Global</option>
            @foreach ($pembukuanList as $pembukuan)
                <option value="{{ $pembukuan->id }}">{{ $pembukuan->nama }}</option>
            @endforeach
        </select>

        <select wire:model.live="sort" class="rounded-xl border border-slate-200 bg-white px-2.5 py-2 text-sm text-slate-700 shadow-sm shadow-slate-900/5 transition-colors focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-600/10">
            <option value="nama_asc">Nama (A-Z)</option>
            <option value="nama_desc">Nama (Z-A)</option>
            <option value="terbaru">Terbaru ditambahkan</option>
            <option value="terlama">Terlama ditambahkan</option>
        </select>
    </div>

    {{-- List kategori --}}
    <div class="space-y-2">
        @forelse ($kategoriList as $kategori)
            <div wire:key="kategori-{{ $kategori->id }}" class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm shadow-slate-900/5">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-sm sm:text-base font-semibold text-slate-900">{{ $kategori->nama }}</p>
                        <div class="mt-1.5 flex gap-1.5">
                            <span class="inline-block rounded-full px-2.5 py-1 text-xs font-semibold
                                {{ $kategori->tipe === \App\Enums\TipeTransaksi::Pemasukan ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700' }}">
                                {{ $kategori->tipe->label() }}
                            </span>
                            @include('livewire._badge-pembukuan', ['pembukuan' => $kategori->pembukuan])
                        </div>
                    </div>

                    <div class="flex shrink-0 items-center gap-2 text-sm">
                        <button
                            wire:click="edit({{ $kategori->id }})"
                            class="rounded-xl border border-slate-200 bg-white px-3 py-1.5 font-semibold text-slate-700 transition-all duration-200 hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600/20 motion-safe:active:scale-[0.97]"
                        >
                            Edit
                        </button>
                        <button
                            wire:click="confirmHapus({{ $kategori->id }})"
                            class="rounded-xl border border-red-200 bg-red-50 px-3 py-1.5 font-semibold text-red-700 transition-all duration-200 hover:bg-red-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-600/30 motion-safe:active:scale-[0.97]"
                        >
                            Hapus
                        </button>
                    </div>

                    @if ($confirmingDeleteId === $kategori->id)
                        @include('livewire._modal-konfirmasi-hapus', [
                            'judul' => 'Hapus Kategori',
                            'pesan' => 'Hapus kategori "'.$kategori->nama.'"? Tindakan ini tidak bisa dibatalkan.',
                            'konfirmAction' => 'hapus('.$kategori->id.')',
                            'batalAction' => 'batalHapus',
                        ])
                    @endif
                </div>
            </div>
        @empty
            <p class="text-sm text-slate-400 text-center py-6">
                {{ $search !== '' ? 'Gak ada kategori yang cocok dengan pencarian.' : 'Belum ada kategori untuk filter ini.' }}
            </p>
        @endforelse
    </div>

    @if ($kategoriList->hasPages())
        <div class="pt-2">
            {{ $kategoriList->links() }}
        </div>
    @endif
</div>
