{{--
    Partial kartu satu baris hutang (bon yang diterima pembukuan ini). Section
    Piutang sengaja dihapus dari tampilan (permintaan client, lihat
    docs/DECISION_LOG.md 28 Agt 2026), jadi partial ini sekarang cuma
    dipakai buat satu arah: bon yang diterima, dari sisi debitur.
    Variabel yang diharapkan: $hp. $melunasiId, $confirmingDeleteId, dst
    tetap property komponen (otomatis kebawa scope).
--}}
<div wire:key="hutang-{{ $hp->id }}" class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
            <p class="text-sm sm:text-base font-medium text-slate-900 truncate">Dari {{ $hp->dariPembukuan->nama }}</p>
            <p class="text-xs text-slate-500 mt-1 truncate">
                {{ $hp->tanggal->translatedFormat('d M Y') }}
                @if ($hp->keterangan)
                    &middot; {{ $hp->keterangan }}
                @endif
            </p>
        </div>
        <div class="text-right shrink-0 max-w-[45%]">
            <p class="font-semibold text-slate-900 tabular-nums break-words">Rp{{ number_format($hp->jumlah, 0, ',', '.') }}</p>
            <span class="inline-block rounded-full px-2 py-0.5 text-xs font-medium break-words
                {{ $hp->status === \App\Enums\StatusHutangPiutang::Lunas ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                {{ $hp->status === \App\Enums\StatusHutangPiutang::Lunas ? 'Lunas' : 'Sisa Rp'.number_format($hp->sisaOutstanding(), 0, ',', '.') }}
            </span>
        </div>
    </div>

    @if ($melunasiId === $hp->id)
        <form wire:submit="simpanPelunasan" class="mt-3 pt-3 border-t border-slate-100 space-y-2">
            {{-- Jumlah & tanggal ditumpuk vertikal di layar sempit (bukan sebaris) - input
                 type="date" punya lebar minimum bawaan browser yang beda-beda, kalau
                 dipaksa sebaris di kartu sempit bisa nyembul keluar dari kartu --}}
            <div class="flex flex-col sm:flex-row gap-2">
                <div class="w-full sm:flex-1">
                    @include('livewire._input-uang', ['field' => 'jumlahPelunasan'])
                </div>
                <input type="date" wire:model="tanggalPelunasan"
                    class="w-full sm:w-auto rounded-lg border px-3 py-2 text-sm transition-colors focus:outline-none focus:ring-2
                        {{ $errors->has('tanggalPelunasan') ? 'border-red-400 focus:border-red-500 focus:ring-red-500/10' : 'border-slate-200 focus:border-blue-400 focus:ring-blue-600/10' }}">
            </div>
            @error('jumlahPelunasan') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
            @error('tanggalPelunasan') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
            <input type="text" wire:model="keteranganPelunasan" placeholder="Keterangan (opsional)"
                class="w-full rounded-lg border px-3 py-2 text-sm transition-colors focus:outline-none focus:ring-2
                    {{ $errors->has('keteranganPelunasan') ? 'border-red-400 focus:border-red-500 focus:ring-red-500/10' : 'border-slate-200 focus:border-blue-400 focus:ring-blue-600/10' }}">
            <div class="flex gap-2">
                <button type="submit" wire:loading.attr="disabled" wire:target="simpanPelunasan"
                    class="rounded-lg bg-emerald-700 px-3 py-2 text-sm font-medium text-white transition-colors hover:bg-emerald-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-700/30 disabled:opacity-50">
                    Catat Pelunasan
                </button>
                <button type="button" wire:click="batalMelunasi"
                    class="rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700 transition-colors hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600/20">
                    Batal
                </button>
            </div>
        </form>
    @else
        <div class="mt-2.5 flex flex-wrap items-center gap-2">
            @if ($hp->status !== \App\Enums\StatusHutangPiutang::Lunas)
                <button
                    wire:click="melunasi({{ $hp->id }})"
                    class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-sm font-medium text-emerald-700 transition-colors hover:bg-emerald-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-700/30"
                >
                    Catat Pelunasan
                </button>
            @endif
            <button
                wire:click="edit({{ $hp->id }})"
                class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-sm font-medium text-slate-700 transition-colors hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600/20"
            >
                Edit
            </button>
            <button
                wire:click="confirmHapus({{ $hp->id }})"
                class="rounded-lg border border-red-200 bg-red-50 px-3 py-1.5 text-sm font-medium text-red-700 transition-colors hover:bg-red-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-600/30"
            >
                Hapus
            </button>
        </div>
    @endif

    @if ($confirmingDeleteId === $hp->id)
        @include('livewire._modal-konfirmasi-hapus', [
            'judul' => 'Hapus Bon',
            'pesan' => 'Hapus bon dari '.$hp->dariPembukuan->nama.' sebesar Rp'.number_format($hp->jumlah, 0, ',', '.').'? '
                .($hp->pelunasan->isNotEmpty() ? 'Bon ini sudah ada pelunasan sebagian, saldo terkait akan otomatis kembali normal. ' : '')
                .'Tindakan ini tidak bisa dibatalkan.',
            'konfirmAction' => 'hapus('.$hp->id.')',
            'batalAction' => 'batalHapus',
        ])
    @endif
</div>
