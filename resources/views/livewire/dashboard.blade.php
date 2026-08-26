<div class="space-y-4">
    <h1 class="text-xl font-semibold text-slate-900">Dashboard</h1>

    {{-- Kartu saldo tiap pembukuan --}}
    <div class="grid grid-cols-3 gap-3">
        @foreach ($pembukuanList as $p)
            <button
                wire:click="pilihPembukuan({{ $p->id }})"
                class="rounded-lg border p-3 text-left
                    {{ $pembukuanTerpilih->id === $p->id ? 'border-slate-900 bg-white' : 'border-slate-200 bg-white hover:border-slate-300' }}"
            >
                <p class="text-xs text-slate-500">{{ $p->nama }}</p>
                <p class="mt-1 font-semibold text-slate-900 text-sm sm:text-base">
                    Rp{{ number_format($p->saldo(), 0, ',', '.') }}
                </p>
            </button>
        @endforeach
    </div>

    {{-- Ringkasan hutang-piutang outstanding pembukuan terpilih --}}
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

    {{-- Riwayat gabungan pembukuan terpilih --}}
    <div>
        <h2 class="text-sm font-semibold text-slate-700 mb-2">
            Riwayat {{ $pembukuanTerpilih->nama }}
        </h2>
        <div class="space-y-2">
            @forelse ($riwayat as $item)
                <div class="rounded-lg border border-slate-200 bg-white p-3 flex items-center justify-between gap-3">
                    <div>
                        <p class="font-medium text-slate-900">{{ $item['deskripsi'] }}</p>
                        <p class="text-xs text-slate-500 mt-0.5">{{ $item['tanggal']->translatedFormat('d M Y') }}</p>
                    </div>
                    <p class="font-semibold shrink-0 {{ $item['arah'] === 'masuk' ? 'text-emerald-700' : 'text-red-700' }}">
                        {{ $item['arah'] === 'masuk' ? '+' : '-' }}Rp{{ number_format($item['jumlah'], 0, ',', '.') }}
                    </p>
                </div>
            @empty
                <p class="text-sm text-slate-500 text-center py-6">Belum ada riwayat untuk pembukuan ini.</p>
            @endforelse
        </div>
    </div>
</div>
