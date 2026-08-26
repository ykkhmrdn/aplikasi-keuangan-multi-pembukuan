<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-xl sm:text-2xl font-semibold tracking-tight text-slate-900">Transfer Saldo</h1>
        @unless ($showForm)
            <button
                wire:click="tambah"
                class="rounded-lg bg-slate-900 px-3 py-2 text-sm font-medium text-white transition-colors hover:bg-slate-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900/30"
            >
                + Transfer
            </button>
        @endunless
    </div>

    {{-- Form transfer --}}
    @if ($showForm)
        <form wire:submit="simpan" class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm space-y-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Dari Pembukuan</label>
                <select
                    wire:model="dariPembukuanId"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-slate-900 transition-colors focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-900/10"
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
                    class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-slate-900 transition-colors focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-900/10"
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

    {{-- Riwayat transfer. Nama pembukuan dikasih badge warna identitas
         (sama kayak Dashboard/Kategori) supaya arah transfer kebaca sekilas. --}}
    @php
        $badgePembukuan = [
            'pribadi' => 'bg-indigo-50 text-indigo-700',
            'usaha' => 'bg-teal-50 text-teal-700',
            'kantor' => 'bg-violet-50 text-violet-700',
        ];
    @endphp
    <div class="space-y-2">
        @forelse ($transferList as $transfer)
            <div wire:key="transfer-{{ $transfer->id }}" class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <span class="inline-block rounded-full px-2 py-0.5 text-xs font-medium {{ $badgePembukuan[$transfer->dariPembukuan->tipe->value] }}">
                                {{ $transfer->dariPembukuan->nama }}
                            </span>
                            <span class="text-slate-400">&rarr;</span>
                            <span class="inline-block rounded-full px-2 py-0.5 text-xs font-medium {{ $badgePembukuan[$transfer->kePembukuan->tipe->value] }}">
                                {{ $transfer->kePembukuan->nama }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 mt-1.5 truncate">
                            {{ $transfer->tanggal->translatedFormat('d M Y') }}
                            @if ($transfer->keterangan)
                                &middot; {{ $transfer->keterangan }}
                            @endif
                        </p>
                    </div>

                    <p class="font-semibold text-slate-900 shrink-0 tabular-nums break-words max-w-[40%] text-right">
                        Rp{{ number_format($transfer->jumlah, 0, ',', '.') }}
                    </p>
                </div>
            </div>
        @empty
            <p class="text-sm text-slate-500 text-center py-6">Belum ada riwayat transfer.</p>
        @endforelse
    </div>
</div>
