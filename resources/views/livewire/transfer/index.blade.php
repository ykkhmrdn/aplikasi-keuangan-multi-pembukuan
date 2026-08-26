<div class="space-y-4">
    <div class="flex items-center justify-between">
        <h1 class="text-xl font-semibold text-slate-900">Transfer Saldo</h1>
        @unless ($showForm)
            <button
                wire:click="tambah"
                class="rounded-lg bg-slate-900 px-3 py-1.5 text-sm font-medium text-white hover:bg-slate-800"
            >
                + Transfer
            </button>
        @endunless
    </div>

    {{-- Form transfer --}}
    @if ($showForm)
        <form wire:submit="simpan" class="rounded-lg border border-slate-200 bg-white p-4 space-y-3">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Dari Pembukuan</label>
                <select
                    wire:model="dariPembukuanId"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500"
                >
                    <option value="">Pilih pembukuan asal</option>
                    @foreach ($pembukuanList as $pembukuan)
                        <option value="{{ $pembukuan->id }}">{{ $pembukuan->nama }}</option>
                    @endforeach
                </select>
                @error('dariPembukuanId') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Ke Pembukuan</label>
                <select
                    wire:model="kePembukuanId"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500"
                >
                    <option value="">Pilih pembukuan tujuan</option>
                    @foreach ($pembukuanList as $pembukuan)
                        <option value="{{ $pembukuan->id }}">{{ $pembukuan->nama }}</option>
                    @endforeach
                </select>
                @error('kePembukuanId') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Jumlah</label>
                <input
                    type="number" step="0.01" min="0"
                    wire:model="jumlah"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500"
                >
                @error('jumlah') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Tanggal</label>
                <input
                    type="date"
                    wire:model="tanggal"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500"
                >
                @error('tanggal') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Keterangan (opsional)</label>
                <textarea
                    wire:model="keterangan"
                    rows="2"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500"
                ></textarea>
                @error('keterangan') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex gap-2 pt-1">
                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    wire:target="simpan"
                    class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800 disabled:opacity-50"
                >
                    Simpan
                </button>
                <button
                    type="button"
                    wire:click="batal"
                    class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                >
                    Batal
                </button>
            </div>
        </form>
    @endif

    {{-- Riwayat transfer --}}
    <div class="space-y-2">
        @forelse ($transferList as $transfer)
            <div wire:key="transfer-{{ $transfer->id }}" class="rounded-lg border border-slate-200 bg-white p-3">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="font-medium text-slate-900">
                            {{ $transfer->dariPembukuan->nama }} &rarr; {{ $transfer->kePembukuan->nama }}
                        </p>
                        <p class="text-xs text-slate-500 mt-0.5">
                            {{ $transfer->tanggal->translatedFormat('d M Y') }}
                            @if ($transfer->keterangan)
                                &middot; {{ $transfer->keterangan }}
                            @endif
                        </p>
                    </div>

                    <p class="font-semibold text-slate-900 shrink-0">
                        Rp{{ number_format($transfer->jumlah, 0, ',', '.') }}
                    </p>
                </div>
            </div>
        @empty
            <p class="text-sm text-slate-500 text-center py-6">Belum ada riwayat transfer.</p>
        @endforelse
    </div>
</div>
