<div class="space-y-6">
    <h1 class="text-xl sm:text-2xl font-semibold tracking-tight text-slate-900">Dashboard</h1>

    {{-- Kartu saldo tiap pembukuan, sekaligus switcher. Tiap pembukuan punya warna identitas
         sendiri (indigo/teal/violet) supaya sekali lihat langsung kebedain (docs/DECISION_LOG.md). --}}
    <div class="grid grid-cols-3 gap-3">
        @foreach ($pembukuanList as $p)
            @php
                $aktif = $pembukuanTerpilih->id === $p->id;
                $saldo = $p->saldo();
                // saldo minus tetap boleh (bukan salah otomatis - bisa defisit beneran atau
                // input transaksi gak urut tanggal), tapi tetap harus keliatan jelas, bukan
                // didiemin pakai warna sama kayak saldo positif
                $saldoMinus = bccomp($saldo, '0', 2) < 0;
                $saldoAbs = $saldoMinus ? bcmul($saldo, '-1', 2) : $saldo;
                $accent = match ($p->tipe) {
                    \App\Enums\TipePembukuan::Pribadi => ['dot' => 'bg-indigo-500', 'bar' => 'bg-indigo-500', 'ring' => 'ring-indigo-500/30', 'border' => 'border-indigo-300'],
                    \App\Enums\TipePembukuan::Usaha => ['dot' => 'bg-teal-500', 'bar' => 'bg-teal-500', 'ring' => 'ring-teal-500/30', 'border' => 'border-teal-300'],
                    \App\Enums\TipePembukuan::Kantor => ['dot' => 'bg-violet-500', 'bar' => 'bg-violet-500', 'ring' => 'ring-violet-500/30', 'border' => 'border-violet-300'],
                };
            @endphp
            <button
                wire:click="pilihPembukuan({{ $p->id }})"
                class="relative min-w-0 rounded-xl border bg-white py-3 pl-4 pr-2 text-left shadow-sm transition-colors
                    {{ $aktif ? $accent['border'].' ring-2 '.$accent['ring'] : 'border-slate-200 hover:border-slate-300' }}"
            >
                <span class="absolute inset-y-0 left-0 w-1 rounded-l-xl {{ $accent['bar'] }}"></span>
                <p class="flex items-center gap-1.5 text-xs text-slate-500">
                    <span class="inline-block w-1.5 h-1.5 rounded-full {{ $accent['dot'] }}"></span>
                    {{ $p->nama }}
                </p>
                {{-- break-words + min-w-0 di button: angka gede (ratusan juta+) gak punya spasi
                     buat wrap alami, tanpa ini bisa overflow kepotong di grid 3 kolom sempit --}}
                <p class="mt-1 font-semibold text-sm sm:text-base tabular-nums break-words {{ $saldoMinus ? 'text-red-700' : 'text-slate-900' }}">
                    {{ $saldoMinus ? '-' : '' }}Rp{{ number_format($saldoAbs, 0, ',', '.') }}
                </p>
                @if ($saldoMinus)
                    <p class="mt-0.5 text-[11px] font-medium text-red-600">Saldo minus</p>
                @endif
            </button>
        @endforeach
    </div>

    {{-- Ringkasan hutang-piutang outstanding pembukuan terpilih --}}
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

    {{-- Riwayat gabungan pembukuan terpilih --}}
    <div>
        <h2 class="text-xs font-semibold uppercase tracking-wide text-slate-500 mb-2">
            Riwayat {{ $pembukuanTerpilih->nama }}
        </h2>
        <div class="space-y-2">
            @forelse ($riwayat as $item)
                <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-sm sm:text-base font-medium text-slate-900 truncate">{{ $item['deskripsi'] }}</p>
                        <p class="text-xs text-slate-500 mt-0.5">{{ $item['tanggal']->translatedFormat('d M Y') }}</p>
                    </div>
                    <p class="font-semibold shrink-0 tabular-nums break-words text-right {{ $item['arah'] === 'masuk' ? 'text-emerald-700' : 'text-red-700' }}">
                        {{ $item['arah'] === 'masuk' ? '+' : '-' }}Rp{{ number_format($item['jumlah'], 0, ',', '.') }}
                    </p>
                </div>
            @empty
                <p class="text-sm text-slate-500 text-center py-6">Belum ada riwayat untuk pembukuan ini.</p>
            @endforelse
        </div>
    </div>
</div>
