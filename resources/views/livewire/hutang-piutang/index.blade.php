<div class="space-y-4">
    {{-- Switcher pembukuan --}}
    <div class="flex gap-2">
        @foreach (\App\Enums\TipePembukuan::cases() as $tipePembukuan)
            <a
                href="{{ route('hutang-piutang.index', $tipePembukuan->value) }}"
                class="rounded-full px-3 py-1 text-sm font-medium
                    {{ $pembukuan->tipe === $tipePembukuan ? 'bg-slate-900 text-white' : 'bg-white border border-slate-300 text-slate-600 hover:bg-slate-50' }}"
            >
                {{ $tipePembukuan->label() }}
            </a>
        @endforeach
    </div>

    <div class="flex items-center justify-between">
        <h1 class="text-xl font-semibold text-slate-900">Hutang-Piutang {{ $pembukuan->nama }}</h1>
        @unless ($showForm)
            <button
                wire:click="tambah"
                class="rounded-lg bg-slate-900 px-3 py-1.5 text-sm font-medium text-white hover:bg-slate-800"
            >
                + Catat Bon
            </button>
        @endunless
    </div>

    {{-- Ringkasan outstanding --}}
    <div class="grid grid-cols-2 gap-3">
        <div class="rounded-lg border border-slate-200 bg-white p-3">
            <p class="text-xs text-slate-500">Piutang (belum diterima)</p>
            <p class="text-lg font-semibold text-emerald-700">Rp{{ number_format($piutangOutstanding, 0, ',', '.') }}</p>
        </div>
        <div class="rounded-lg border border-slate-200 bg-white p-3">
            <p class="text-xs text-slate-500">Hutang (belum dibayar)</p>
            <p class="text-lg font-semibold text-red-700">Rp{{ number_format($hutangOutstanding, 0, ',', '.') }}</p>
        </div>
    </div>

    {{-- Form catat bon baru --}}
    @if ($showForm)
        <form wire:submit="simpan" class="rounded-lg border border-slate-200 bg-white p-4 space-y-3">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Pembukuan Pemberi (berpiutang)</label>
                <select
                    wire:model="dariPembukuanId"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500"
                >
                    <option value="">Pilih pembukuan</option>
                    @foreach ($pembukuanList as $p)
                        <option value="{{ $p->id }}">{{ $p->nama }}</option>
                    @endforeach
                </select>
                @error('dariPembukuanId') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Pembukuan Penerima (berutang)</label>
                <select
                    wire:model="kePembukuanId"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500"
                >
                    <option value="">Pilih pembukuan</option>
                    @foreach ($pembukuanList as $p)
                        <option value="{{ $p->id }}">{{ $p->nama }}</option>
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

    {{-- Section: Piutang (yang diberikan pembukuan ini) --}}
    <div>
        <h2 class="text-sm font-semibold text-slate-700 mb-2">Piutang &mdash; bon yang diberikan</h2>
        <div class="space-y-2">
            @forelse ($piutangList as $hp)
                <div wire:key="piutang-{{ $hp->id }}" class="rounded-lg border border-slate-200 bg-white p-3">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="font-medium text-slate-900">Ke {{ $hp->kePembukuan->nama }}</p>
                            <p class="text-xs text-slate-500 mt-0.5">
                                {{ $hp->tanggal->translatedFormat('d M Y') }}
                                @if ($hp->keterangan)
                                    &middot; {{ $hp->keterangan }}
                                @endif
                            </p>
                        </div>
                        <div class="text-right shrink-0">
                            <p class="font-semibold text-slate-900">Rp{{ number_format($hp->jumlah, 0, ',', '.') }}</p>
                            <span class="inline-block rounded-full px-2 py-0.5 text-xs font-medium
                                {{ $hp->status === \App\Enums\StatusHutangPiutang::Lunas ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                {{ $hp->status === \App\Enums\StatusHutangPiutang::Lunas ? 'Lunas' : 'Sisa Rp'.number_format($hp->sisaOutstanding(), 0, ',', '.') }}
                            </span>
                        </div>
                    </div>

                    @if ($hp->status !== \App\Enums\StatusHutangPiutang::Lunas)
                        @if ($melunasiId === $hp->id)
                            <form wire:submit="simpanPelunasan" class="mt-3 pt-3 border-t border-slate-100 space-y-2">
                                <div class="flex gap-2">
                                    <input type="number" step="0.01" min="0" wire:model="jumlahPelunasan"
                                        class="flex-1 rounded-lg border border-slate-300 px-3 py-1.5 text-sm">
                                    <input type="date" wire:model="tanggalPelunasan"
                                        class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm">
                                </div>
                                @error('jumlahPelunasan') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                                @error('tanggalPelunasan') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                                <input type="text" wire:model="keteranganPelunasan" placeholder="Keterangan (opsional)"
                                    class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-sm">
                                <div class="flex gap-2">
                                    <button type="submit" wire:loading.attr="disabled" wire:target="simpanPelunasan"
                                        class="rounded-lg bg-emerald-700 px-3 py-1.5 text-sm font-medium text-white hover:bg-emerald-800 disabled:opacity-50">
                                        Catat Pelunasan
                                    </button>
                                    <button type="button" wire:click="batalMelunasi"
                                        class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm text-slate-700 hover:bg-slate-50">
                                        Batal
                                    </button>
                                </div>
                            </form>
                        @else
                            <button wire:click="melunasi({{ $hp->id }})" class="mt-2 text-sm text-emerald-700 hover:text-emerald-900 font-medium">
                                Catat Pelunasan
                            </button>
                        @endif
                    @endif
                </div>
            @empty
                <p class="text-sm text-slate-500 text-center py-4">Belum ada piutang.</p>
            @endforelse
        </div>
    </div>

    {{-- Section: Hutang (yang diterima pembukuan ini) --}}
    <div>
        <h2 class="text-sm font-semibold text-slate-700 mb-2">Hutang &mdash; bon yang diterima</h2>
        <div class="space-y-2">
            @forelse ($hutangList as $hp)
                <div wire:key="hutang-{{ $hp->id }}" class="rounded-lg border border-slate-200 bg-white p-3">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="font-medium text-slate-900">Dari {{ $hp->dariPembukuan->nama }}</p>
                            <p class="text-xs text-slate-500 mt-0.5">
                                {{ $hp->tanggal->translatedFormat('d M Y') }}
                                @if ($hp->keterangan)
                                    &middot; {{ $hp->keterangan }}
                                @endif
                            </p>
                        </div>
                        <div class="text-right shrink-0">
                            <p class="font-semibold text-slate-900">Rp{{ number_format($hp->jumlah, 0, ',', '.') }}</p>
                            <span class="inline-block rounded-full px-2 py-0.5 text-xs font-medium
                                {{ $hp->status === \App\Enums\StatusHutangPiutang::Lunas ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                {{ $hp->status === \App\Enums\StatusHutangPiutang::Lunas ? 'Lunas' : 'Sisa Rp'.number_format($hp->sisaOutstanding(), 0, ',', '.') }}
                            </span>
                        </div>
                    </div>

                    @if ($hp->status !== \App\Enums\StatusHutangPiutang::Lunas)
                        @if ($melunasiId === $hp->id)
                            <form wire:submit="simpanPelunasan" class="mt-3 pt-3 border-t border-slate-100 space-y-2">
                                <div class="flex gap-2">
                                    <input type="number" step="0.01" min="0" wire:model="jumlahPelunasan"
                                        class="flex-1 rounded-lg border border-slate-300 px-3 py-1.5 text-sm">
                                    <input type="date" wire:model="tanggalPelunasan"
                                        class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm">
                                </div>
                                @error('jumlahPelunasan') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                                @error('tanggalPelunasan') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                                <input type="text" wire:model="keteranganPelunasan" placeholder="Keterangan (opsional)"
                                    class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-sm">
                                <div class="flex gap-2">
                                    <button type="submit" wire:loading.attr="disabled" wire:target="simpanPelunasan"
                                        class="rounded-lg bg-emerald-700 px-3 py-1.5 text-sm font-medium text-white hover:bg-emerald-800 disabled:opacity-50">
                                        Catat Pelunasan
                                    </button>
                                    <button type="button" wire:click="batalMelunasi"
                                        class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm text-slate-700 hover:bg-slate-50">
                                        Batal
                                    </button>
                                </div>
                            </form>
                        @else
                            <button wire:click="melunasi({{ $hp->id }})" class="mt-2 text-sm text-emerald-700 hover:text-emerald-900 font-medium">
                                Catat Pelunasan
                            </button>
                        @endif
                    @endif
                </div>
            @empty
                <p class="text-sm text-slate-500 text-center py-4">Belum ada hutang.</p>
            @endforelse
        </div>
    </div>
</div>
