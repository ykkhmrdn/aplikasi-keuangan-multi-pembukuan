<div class="space-y-6">
    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">Dashboard</h1>

    {{-- Kartu saldo tiap pembukuan, sekaligus switcher - SIGNATURE ELEMENT aplikasi ini
         (satu-satunya tempat 3 pembukuan tampil sekaligus). Kartu aktif dapet gradient fill
         penuh + shadow berwarna (bukan cuma border kayak sebelumnya), kartu non-aktif tetap
         putih/quiet - biar yang aktif jelas paling menonjol. Warna gradient sesuai identitas
         pembukuan (indigo/teal/violet), dipertahankan dari sistem yang sudah ada. --}}
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
                    \App\Enums\TipePembukuan::Pribadi => ['dot' => 'bg-indigo-500', 'gradient' => 'bg-gradient-to-br from-indigo-600 to-indigo-500', 'shadow' => 'shadow-lg shadow-indigo-600/25', 'label' => 'text-indigo-100'],
                    \App\Enums\TipePembukuan::Usaha => ['dot' => 'bg-teal-500', 'gradient' => 'bg-gradient-to-br from-teal-600 to-teal-500', 'shadow' => 'shadow-lg shadow-teal-600/25', 'label' => 'text-teal-100'],
                    \App\Enums\TipePembukuan::Kantor => ['dot' => 'bg-violet-500', 'gradient' => 'bg-gradient-to-br from-violet-600 to-violet-500', 'shadow' => 'shadow-lg shadow-violet-600/25', 'label' => 'text-violet-100'],
                };
            @endphp
            <button
                wire:click="pilihPembukuan({{ $p->id }})"
                class="relative min-w-0 rounded-2xl py-3.5 px-3.5 text-left transition-all duration-200 motion-safe:active:scale-[0.97]
                    {{ $aktif ? $accent['gradient'].' '.$accent['shadow'] : 'bg-white border border-slate-200 shadow-sm shadow-slate-900/5 hover:border-slate-300' }}"
            >
                <p class="flex items-center gap-1.5 text-xs font-medium {{ $aktif ? $accent['label'] : 'text-slate-500' }}">
                    @unless ($aktif)
                        <span class="inline-block w-1.5 h-1.5 rounded-full {{ $accent['dot'] }}"></span>
                    @endunless
                    {{ $p->nama }}
                </p>
                {{-- break-words + min-w-0 di button: angka gede (ratusan juta+) gak punya spasi
                     buat wrap alami, tanpa ini bisa overflow kepotong di grid 3 kolom sempit --}}
                <p class="mt-1.5 font-bold text-sm sm:text-lg tabular-nums break-words
                    {{ $aktif ? 'text-white' : ($saldoMinus ? 'text-red-700' : 'text-slate-900') }}">
                    {{ $saldoMinus ? '-' : '' }}Rp{{ number_format($saldoAbs, 0, ',', '.') }}
                </p>
                @if ($saldoMinus)
                    <p class="mt-0.5 text-[11px] font-semibold {{ $aktif ? 'text-red-100' : 'text-red-600' }}">Saldo minus</p>
                @endif
            </button>
        @endforeach
    </div>

    {{-- Ringkasan hutang-piutang outstanding pembukuan terpilih --}}
    <div class="grid grid-cols-2 gap-3">
        <div class="min-w-0 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm shadow-slate-900/5">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Piutang (belum diterima)</p>
            <p class="mt-1 text-lg font-bold text-emerald-700 tabular-nums break-words">Rp{{ number_format($piutangOutstanding, 0, ',', '.') }}</p>
        </div>
        <div class="min-w-0 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm shadow-slate-900/5">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Hutang (belum dibayar)</p>
            <p class="mt-1 text-lg font-bold text-red-700 tabular-nums break-words">Rp{{ number_format($hutangOutstanding, 0, ',', '.') }}</p>
        </div>
    </div>

    {{-- Riwayat gabungan pembukuan terpilih --}}
    <div>
        <h2 class="text-xs font-semibold uppercase tracking-wide text-slate-500 mb-2">
            Riwayat {{ $pembukuanTerpilih->nama }}
        </h2>
        <div class="space-y-2">
            @forelse ($riwayat as $item)
                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm shadow-slate-900/5 flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-sm sm:text-base font-semibold text-slate-900 truncate">{{ $item['deskripsi'] }}</p>
                        <p class="text-xs text-slate-500 mt-0.5">{{ $item['tanggal']->translatedFormat('d M Y') }}</p>
                    </div>
                    <p class="font-bold shrink-0 tabular-nums break-words text-right {{ $item['arah'] === 'masuk' ? 'text-emerald-700' : 'text-red-700' }}">
                        {{ $item['arah'] === 'masuk' ? '+' : '-' }}Rp{{ number_format($item['jumlah'], 0, ',', '.') }}
                    </p>
                </div>
            @empty
                <p class="text-sm text-slate-500 text-center py-6">Belum ada riwayat untuk pembukuan ini.</p>
            @endforelse
        </div>
    </div>
</div>
