<div class="space-y-6">
    @include('livewire._pill-switcher', ['pembukuan' => $pembukuan, 'routeName' => 'hutang-piutang.index'])

    @include('livewire._page-header', [
        'judul' => 'Hutang '.$pembukuan->nama,
        'tombolLabel' => '+ Catat Bon',
        'tombolAction' => 'tambah',
    ])

    {{-- Ringkasan outstanding - Piutang sengaja dihapus dari tampilan (permintaan
         client, lihat docs/DECISION_LOG.md 28 Agt 2026), cuma Hutang yang tersisa --}}
    <div class="rounded-xl border border-slate-200 bg-gradient-to-br from-white to-blue-50/70 p-4 shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Hutang (belum dibayar)</p>
        <p class="mt-1 text-lg font-semibold text-red-700 tabular-nums break-words">Rp{{ number_format($hutangOutstanding, 0, ',', '.') }}</p>
    </div>

    {{-- Form catat/edit bon --}}
    @if ($showForm)
        <form wire:submit="simpan" class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm space-y-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Pembukuan Pemberi</label>
                <select
                    wire:model="dariPembukuanId"
                    class="w-full rounded-lg border px-3 py-2.5 text-slate-900 transition-colors focus:outline-none focus:ring-2
                        {{ $errors->has('dariPembukuanId') ? 'border-red-400 focus:border-red-500 focus:ring-red-500/10' : 'border-slate-200 focus:border-blue-400 focus:ring-blue-600/10' }}"
                >
                    <option value="">Pilih pembukuan</option>
                    @foreach ($pembukuanList as $p)
                        <option value="{{ $p->id }}">{{ $p->nama }}</option>
                    @endforeach
                </select>
                @error('dariPembukuanId') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Pembukuan Penerima (berutang)</label>
                <select
                    wire:model="kePembukuanId"
                    class="w-full rounded-lg border px-3 py-2.5 text-slate-900 transition-colors focus:outline-none focus:ring-2
                        {{ $errors->has('kePembukuanId') ? 'border-red-400 focus:border-red-500 focus:ring-red-500/10' : 'border-slate-200 focus:border-blue-400 focus:ring-blue-600/10' }}"
                >
                    <option value="">Pilih pembukuan</option>
                    @foreach ($pembukuanList as $p)
                        <option value="{{ $p->id }}">{{ $p->nama }}</option>
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
                    class="w-full rounded-lg border px-3 py-2.5 text-slate-900 transition-colors focus:outline-none focus:ring-2
                        {{ $errors->has('tanggal') ? 'border-red-400 focus:border-red-500 focus:ring-red-500/10' : 'border-slate-200 focus:border-blue-400 focus:ring-blue-600/10' }}"
                >
                @error('tanggal') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Keterangan (opsional)</label>
                <textarea
                    wire:model="keterangan"
                    rows="2"
                    class="w-full rounded-lg border px-3 py-2.5 text-slate-900 transition-colors focus:outline-none focus:ring-2
                        {{ $errors->has('keterangan') ? 'border-red-400 focus:border-red-500 focus:ring-red-500/10' : 'border-slate-200 focus:border-blue-400 focus:ring-blue-600/10' }}"
                ></textarea>
                @error('keterangan') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex gap-2 pt-1">
                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    wire:target="simpan"
                    class="rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-medium text-white transition-colors hover:bg-blue-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600/30 disabled:opacity-50"
                >
                    Simpan
                </button>
                <button
                    type="button"
                    wire:click="batal"
                    class="rounded-lg border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-700 transition-colors hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600/20"
                >
                    Batal
                </button>
            </div>
        </form>
    @endif

    @include('livewire._search-bar', ['placeholder' => 'Cari keterangan atau nama pembukuan...'])

    <div class="flex flex-wrap gap-2">
        <select wire:model.live="sort" class="rounded-lg border border-slate-200 bg-white px-2.5 py-2 text-sm text-slate-700 shadow-sm transition-colors focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-600/10">
            <option value="tanggal_terbaru">Tanggal terbaru</option>
            <option value="tanggal_terlama">Tanggal terlama</option>
            <option value="jumlah_terbesar">Jumlah terbesar</option>
            <option value="jumlah_terkecil">Jumlah terkecil</option>
        </select>
    </div>

    {{-- Daftar hutang (bon yang diterima pembukuan ini) --}}
    <div class="space-y-2">
        @forelse ($hutangList as $hp)
            @include('livewire.hutang-piutang._kartu-bon', ['hp' => $hp])
        @empty
            <p class="text-sm text-slate-500 text-center py-6">
                {{ $search !== '' ? 'Gak ada hutang yang cocok dengan pencarian.' : 'Belum ada hutang.' }}
            </p>
        @endforelse
    </div>

    @if ($hutangList->hasPages())
        <div class="pt-2">
            {{ $hutangList->links() }}
        </div>
    @endif
</div>
