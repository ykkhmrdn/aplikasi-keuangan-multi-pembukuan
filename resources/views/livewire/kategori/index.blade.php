<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-xl sm:text-2xl font-semibold tracking-tight text-slate-900">Kategori</h1>
        @unless ($showForm)
            <button
                wire:click="tambah"
                class="rounded-lg bg-slate-900 px-3 py-2 text-sm font-medium text-white transition-colors hover:bg-slate-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900/30"
            >
                + Tambah
            </button>
        @endunless
    </div>

    @if ($deleteErrorMessage)
        <div class="rounded-xl bg-red-50 border border-red-200 px-3 py-2.5 text-sm text-red-700">
            {{ $deleteErrorMessage }}
        </div>
    @endif

    {{-- Form tambah/edit --}}
    @if ($showForm)
        <form wire:submit="simpan" class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm space-y-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Nama</label>
                <input
                    type="text"
                    wire:model="nama"
                    autofocus
                    class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-slate-900 transition-colors focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-900/10"
                >
                @error('nama') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Tipe</label>
                <select
                    wire:model="tipe"
                    @if ($editingLocked) disabled @endif
                    class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-slate-900 transition-colors focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-900/10 disabled:bg-slate-100 disabled:text-slate-500"
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
                    class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-slate-900 transition-colors focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-900/10"
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
        <select wire:model.live="filterTipe" class="rounded-lg border border-slate-300 px-2.5 py-2 text-sm text-slate-700 transition-colors focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-900/10">
            <option value="semua">Semua tipe</option>
            @foreach ($tipeOptions as $option)
                <option value="{{ $option->value }}">{{ $option->label() }}</option>
            @endforeach
        </select>

        <select wire:model.live="filterPembukuan" class="rounded-lg border border-slate-300 px-2.5 py-2 text-sm text-slate-700 transition-colors focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-900/10">
            <option value="semua">Semua pembukuan</option>
            <option value="global">Global</option>
            @foreach ($pembukuanList as $pembukuan)
                <option value="{{ $pembukuan->id }}">{{ $pembukuan->nama }}</option>
            @endforeach
        </select>
    </div>

    {{-- List kategori. Badge pembukuan pakai warna identitas yang sama dengan Dashboard,
         supaya kelihatan kategori itu punya pembukuan mana sekilas lihat. --}}
    @php
        $badgePembukuan = [
            'pribadi' => 'bg-indigo-50 text-indigo-700',
            'usaha' => 'bg-teal-50 text-teal-700',
            'kantor' => 'bg-violet-50 text-violet-700',
        ];
    @endphp
    <div class="space-y-2">
        @forelse ($kategoriList as $kategori)
            <div wire:key="kategori-{{ $kategori->id }}" class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-sm sm:text-base font-medium text-slate-900">{{ $kategori->nama }}</p>
                        <div class="mt-1.5 flex gap-1.5">
                            <span class="inline-block rounded-full px-2 py-0.5 text-xs font-medium
                                {{ $kategori->tipe === \App\Enums\TipeTransaksi::Pemasukan ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700' }}">
                                {{ $kategori->tipe->label() }}
                            </span>
                            <span class="inline-block rounded-full px-2 py-0.5 text-xs font-medium
                                {{ $kategori->pembukuan ? $badgePembukuan[$kategori->pembukuan->tipe->value] : 'bg-slate-100 text-slate-600' }}">
                                {{ $kategori->pembukuan->nama ?? 'Global' }}
                            </span>
                        </div>
                    </div>

                    <div class="flex shrink-0 items-center gap-3 text-sm">
                        <button wire:click="edit({{ $kategori->id }})" class="text-slate-500 hover:text-slate-800 focus-visible:outline-none focus-visible:underline">Edit</button>

                        @if ($confirmingDeleteId === $kategori->id)
                            <span class="inline-flex items-center gap-2 rounded-lg bg-red-50 px-2 py-1">
                                <span class="text-xs text-red-700">Yakin?</span>
                                <button wire:click="hapus({{ $kategori->id }})" class="text-xs font-semibold text-red-700 hover:text-red-900 focus-visible:outline-none focus-visible:underline">Ya</button>
                                <button wire:click="batalHapus" class="text-xs text-slate-500 hover:text-slate-800 focus-visible:outline-none focus-visible:underline">Batal</button>
                            </span>
                        @else
                            <button wire:click="confirmHapus({{ $kategori->id }})" class="text-red-600 hover:text-red-800 focus-visible:outline-none focus-visible:underline">Hapus</button>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <p class="text-sm text-slate-500 text-center py-6">Belum ada kategori untuk filter ini.</p>
        @endforelse
    </div>
</div>
