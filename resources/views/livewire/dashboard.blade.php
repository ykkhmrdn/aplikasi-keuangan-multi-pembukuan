<div class="space-y-6">
    <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-white">Dashboard</h1>

    {{-- Kartu saldo tiap pembukuan, sekaligus switcher - SIGNATURE ELEMENT aplikasi ini
         (satu-satunya tempat 3 pembukuan tampil sekaligus). Kartu aktif dapet gradient fill
         biru + shadow biru, kartu non-aktif tetap putih/quiet. Warna Usaha/Kantor
         (teal/violet) dipertahankan buat bedain identitas 3 pembukuan. --}}
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
                    \App\Enums\TipePembukuan::Pribadi => ['dot' => 'bg-blue-500', 'gradient' => 'bg-gradient-to-br from-blue-600 to-blue-500', 'shadow' => 'shadow-lg shadow-blue-600/25', 'label' => 'text-blue-100'],
                    \App\Enums\TipePembukuan::Usaha => ['dot' => 'bg-teal-500', 'gradient' => 'bg-gradient-to-br from-teal-600 to-teal-500', 'shadow' => 'shadow-lg shadow-teal-600/25', 'label' => 'text-teal-100'],
                    \App\Enums\TipePembukuan::Kantor => ['dot' => 'bg-violet-500', 'gradient' => 'bg-gradient-to-br from-violet-600 to-violet-500', 'shadow' => 'shadow-lg shadow-violet-600/25', 'label' => 'text-violet-100'],
                };
            @endphp
            <button
                wire:click="pilihPembukuan({{ $p->id }})"
                class="relative min-w-0 rounded-xl py-3.5 px-3.5 text-left transition-all duration-200 motion-safe:active:scale-[0.97]
                    {{ $aktif ? $accent['gradient'].' '.$accent['shadow'] : 'bg-white border border-slate-200 shadow-sm hover:border-slate-300' }}"
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

    {{-- Hutang outstanding pembukuan terpilih, detail per-item (dari pembukuan mana,
         berapa, sisa berapa) - client minta gak cukup cuma total (meeting 28 Agt 2026).
         Piutang sengaja gak ditampilkan lagi di sini, sesuai permintaan client. --}}
    <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="flex items-center justify-between gap-3">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Hutang (belum dibayar)</p>
            <p class="text-lg font-bold text-red-700 tabular-nums break-words">Rp{{ number_format($hutangOutstanding, 0, ',', '.') }}</p>
        </div>

        @if ($hutangDetail->isNotEmpty())
            <div class="mt-3 pt-3 border-t border-slate-100 space-y-2.5">
                @foreach ($hutangDetail as $hp)
                    <div class="flex items-center justify-between gap-3 text-sm">
                        <div class="min-w-0">
                            <p class="font-medium text-slate-900 truncate">Dari {{ $hp->dariPembukuan->nama }}</p>
                            <p class="text-xs text-slate-500 mt-0.5">{{ $hp->tanggal->translatedFormat('d M Y') }}</p>
                        </div>
                        <p class="shrink-0 font-semibold text-red-700 tabular-nums break-words text-right max-w-[45%]">
                            Rp{{ number_format($hp->sisaOutstanding(), 0, ',', '.') }}
                        </p>
                    </div>
                @endforeach
            </div>
        @else
            <p class="mt-2 text-sm text-slate-500">Tidak ada hutang outstanding untuk pembukuan ini.</p>
        @endif
    </div>

    {{-- Analisis Pengeluaran: breakdown per kategori + filter periode (ganti
         "Riwayat transaksi terbaru", diminta client di meeting 28 Agt 2026) --}}
    <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="flex items-center justify-between gap-3 flex-wrap">
            <h2 class="text-sm font-semibold text-slate-900">Analisis Pengeluaran &mdash; {{ $pembukuanTerpilih->nama }}</h2>
        </div>

        {{-- Filter periode: preset cepat + rentang tanggal custom --}}
        <div class="mt-3 flex flex-wrap gap-2">
            <select wire:model.live="periode" class="rounded-lg border border-slate-200 bg-white px-2.5 py-2 text-sm text-slate-700 transition-colors focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-600/10">
                <option value="harian">Hari ini</option>
                <option value="mingguan">Minggu ini</option>
                <option value="bulanan">Bulan ini</option>
                <option value="tahunan">Tahun ini</option>
                <option value="semua">Semua waktu</option>
                <option value="custom">Rentang tanggal...</option>
            </select>

            @if ($periode === 'custom')
                <input type="date" wire:model.live="tanggalDari" class="rounded-lg border border-slate-200 bg-white px-2.5 py-2 text-sm text-slate-700 transition-colors focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-600/10" placeholder="Dari tanggal">
                <input type="date" wire:model.live="tanggalSampai" class="rounded-lg border border-slate-200 bg-white px-2.5 py-2 text-sm text-slate-700 transition-colors focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-600/10" placeholder="Sampai tanggal">
            @endif
        </div>

        {{-- Breakdown per kategori: bar horizontal + persentase (referensi visual dari
             client, kategori baru tanpa transaksi tetap tampil sebagai Rp0/0%) --}}
        <div class="mt-4 space-y-3">
            @forelse ($analisisPengeluaran as $item)
                <div>
                    <div class="flex items-center justify-between gap-2 text-sm">
                        <p class="font-medium text-slate-700 truncate">{{ $item['kategori'] }}</p>
                        <p class="shrink-0 tabular-nums text-slate-500">
                            Rp{{ number_format($item['jumlah'], 0, ',', '.') }}
                            <span class="text-slate-400">&middot; {{ number_format($item['persen'], 1) }}%</span>
                        </p>
                    </div>
                    <div class="mt-1.5 h-2 w-full rounded-full bg-slate-100 overflow-hidden">
                        <div class="h-full rounded-full bg-gradient-to-r from-blue-600 to-blue-500" style="width: {{ min($item['persen'], 100) }}%"></div>
                    </div>
                </div>
            @empty
                <p class="text-sm text-slate-500 text-center py-6">Belum ada kategori pengeluaran untuk pembukuan ini.</p>
            @endforelse
        </div>
    </div>
</div>
