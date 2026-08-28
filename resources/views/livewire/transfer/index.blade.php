<div class="space-y-6">
    @include('livewire._page-header', ['judul' => 'Transfer Saldo', 'tombolLabel' => '+ Transfer', 'tombolAction' => 'tambah'])

    {{-- Form transfer --}}
    @if ($showForm)
        <form wire:submit="simpan" class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm shadow-slate-900/5 space-y-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Dari Pembukuan</label>
                <select
                    wire:model="dariPembukuanId"
                    class="w-full rounded-xl border px-3 py-2.5 text-slate-900 transition-colors focus:outline-none focus:ring-2
                        {{ $errors->has('dariPembukuanId') ? 'border-red-400 focus:border-red-500 focus:ring-red-500/10' : 'border-slate-200 focus:border-blue-400 focus:ring-blue-600/10' }}"
                >
                    <option value="">Pilih pembukuan asal</option>
                    @foreach ($pembukuanList as $pembukuan)
                        <option value="{{ $pembukuan->id }}">{{ $pembukuan->nama }}</option>
                    @endforeach
                </select>
                @error('dariPembukuanId') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Ke Pembukuan</label>
                <select
                    wire:model="kePembukuanId"
                    class="w-full rounded-xl border px-3 py-2.5 text-slate-900 transition-colors focus:outline-none focus:ring-2
                        {{ $errors->has('kePembukuanId') ? 'border-red-400 focus:border-red-500 focus:ring-red-500/10' : 'border-slate-200 focus:border-blue-400 focus:ring-blue-600/10' }}"
                >
                    <option value="">Pilih pembukuan tujuan</option>
                    @foreach ($pembukuanList as $pembukuan)
                        <option value="{{ $pembukuan->id }}">{{ $pembukuan->nama }}</option>
                    @endforeach
                </select>
                @error('kePembukuanId') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
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

    @include('livewire._search-bar', ['placeholder' => 'Cari keterangan atau nama pembukuan...'])

    <div class="flex flex-wrap gap-2">
        <select wire:model.live="sort" class="rounded-xl border border-slate-200 bg-white px-2.5 py-2 text-sm text-slate-700 shadow-sm shadow-slate-900/5 transition-colors focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-600/10">
            <option value="tanggal_terbaru">Tanggal terbaru</option>
            <option value="tanggal_terlama">Tanggal terlama</option>
            <option value="jumlah_terbesar">Jumlah terbesar</option>
            <option value="jumlah_terkecil">Jumlah terkecil</option>
        </select>
    </div>

    {{-- Riwayat transfer --}}
    <div class="space-y-2">
        @forelse ($transferList as $transfer)
            <div wire:key="transfer-{{ $transfer->id }}" class="rounded-xl border border-slate-200 bg-gradient-to-br from-white to-blue-50/70 p-4 shadow-sm shadow-slate-900/5">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <div class="flex items-center gap-1.5 flex-wrap">
                            @include('livewire._badge-pembukuan', ['pembukuan' => $transfer->dariPembukuan])
                            <span class="text-slate-400">&rarr;</span>
                            @include('livewire._badge-pembukuan', ['pembukuan' => $transfer->kePembukuan])
                        </div>
                        <p class="text-xs text-slate-500 mt-1.5 truncate">
                            {{ $transfer->tanggal->translatedFormat('d M Y') }}
                            @if ($transfer->keterangan)
                                &middot; {{ $transfer->keterangan }}
                            @endif
                        </p>
                    </div>

                    <p class="font-bold text-slate-900 shrink-0 tabular-nums break-words max-w-[40%] text-right">
                        Rp{{ number_format($transfer->jumlah, 0, ',', '.') }}
                    </p>
                </div>
            </div>
        @empty
            <p class="text-sm text-slate-500 text-center py-6">
                {{ $search !== '' ? 'Gak ada riwayat transfer yang cocok dengan pencarian.' : 'Belum ada riwayat transfer.' }}
            </p>
        @endforelse
    </div>

    @if ($transferList->hasPages())
        <div class="pt-2">
            {{ $transferList->links() }}
        </div>
    @endif
</div>
