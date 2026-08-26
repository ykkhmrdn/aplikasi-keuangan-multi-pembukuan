<div class="space-y-6">
    {{-- Switcher pembukuan, warna pill aktif sesuai identitas pembukuan --}}
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
                href="{{ route('hutang-piutang.index', $tipePembukuan->value) }}"
                class="rounded-full px-3 py-1.5 text-sm font-medium transition-colors
                    {{ $pembukuan->tipe === $tipePembukuan ? $accentPill[$tipePembukuan->value] : 'bg-white border border-slate-300 text-slate-600 hover:bg-slate-50' }}"
            >
                {{ $tipePembukuan->label() }}
            </a>
        @endforeach
    </div>

    <div class="flex items-center justify-between">
        <h1 class="text-xl sm:text-2xl font-semibold tracking-tight text-slate-900">Hutang-Piutang {{ $pembukuan->nama }}</h1>
        @unless ($showForm)
            <button
                wire:click="tambah"
                class="rounded-lg bg-slate-900 px-3 py-2 text-sm font-medium text-white transition-colors hover:bg-slate-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900/30"
            >
                + Catat Bon
            </button>
        @endunless
    </div>

    {{-- Ringkasan outstanding --}}
    <div class="grid grid-cols-2 gap-3">
        <div class="min-w-0 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Piutang (belum diterima)</p>
            <p class="mt-1 text-lg font-semibold text-emerald-700 tabular-nums break-words">Rp{{ number_format($piutangOutstanding, 0, ',', '.') }}</p>
        </div>
        <div class="min-w-0 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Hutang (belum dibayar)</p>
            <p class="mt-1 text-lg font-semibold text-red-700 tabular-nums break-words">Rp{{ number_format($hutangOutstanding, 0, ',', '.') }}</p>
        </div>
    </div>

    {{-- Form catat bon baru --}}
    @if ($showForm)
        <form wire:submit="simpan" class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm space-y-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Pembukuan Pemberi (berpiutang)</label>
                <select
                    wire:model="dariPembukuanId"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-slate-900 transition-colors focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-900/10"
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
                    class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-slate-900 transition-colors focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-900/10"
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

    {{-- Section: Piutang (yang diberikan pembukuan ini) --}}
    <div>
        <h2 class="text-xs font-semibold uppercase tracking-wide text-slate-500 mb-2">Piutang &mdash; bon yang diberikan</h2>
        <div class="space-y-2">
            @forelse ($piutangList as $hp)
                @include('livewire.hutang-piutang._kartu-bon', ['hp' => $hp, 'arahLabel' => 'Ke', 'lawan' => $hp->kePembukuan, 'keyPrefix' => 'piutang'])
            @empty
                <p class="text-sm text-slate-500 text-center py-4">Belum ada piutang.</p>
            @endforelse
        </div>
    </div>

    {{-- Section: Hutang (yang diterima pembukuan ini) --}}
    <div>
        <h2 class="text-xs font-semibold uppercase tracking-wide text-slate-500 mb-2">Hutang &mdash; bon yang diterima</h2>
        <div class="space-y-2">
            @forelse ($hutangList as $hp)
                @include('livewire.hutang-piutang._kartu-bon', ['hp' => $hp, 'arahLabel' => 'Dari', 'lawan' => $hp->dariPembukuan, 'keyPrefix' => 'hutang'])
            @empty
                <p class="text-sm text-slate-500 text-center py-4">Belum ada hutang.</p>
            @endforelse
        </div>
    </div>
</div>
