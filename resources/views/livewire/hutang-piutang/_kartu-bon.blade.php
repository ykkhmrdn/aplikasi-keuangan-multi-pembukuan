{{--
    Partial kartu satu baris hutang-piutang, dipakai untuk section Piutang
    maupun Hutang (isinya identik, cuma beda arah label & lawan pembukuan).
    Variabel yang diharapkan: $hp, $arahLabel ('Ke'/'Dari'), $lawan (model Pembukuan).
    $melunasiId, $jumlahPelunasan, dst tetap property komponen (otomatis kebawa scope).
--}}
<div wire:key="{{ $keyPrefix }}-{{ $hp->id }}" class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
            <p class="text-sm sm:text-base font-medium text-slate-900 truncate">{{ $arahLabel }} {{ $lawan->nama }}</p>
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

    @if ($hp->status !== \App\Enums\StatusHutangPiutang::Lunas)
        @if ($melunasiId === $hp->id)
            <form wire:submit="simpanPelunasan" class="mt-3 pt-3 border-t border-slate-100 space-y-2">
                {{-- Jumlah & tanggal ditumpuk vertikal di layar sempit (bukan sebaris) - input
                     type="date" punya lebar minimum bawaan browser yang beda-beda, kalau
                     dipaksa sebaris di kartu sempit bisa nyembul keluar dari kartu --}}
                <div class="flex flex-col sm:flex-row gap-2">
                    <input type="number" step="0.01" min="0" wire:model="jumlahPelunasan"
                        class="w-full sm:flex-1 rounded-lg border border-slate-300 px-3 py-2 text-sm transition-colors focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-900/10">
                    <input type="date" wire:model="tanggalPelunasan"
                        class="w-full sm:w-auto rounded-lg border border-slate-300 px-3 py-2 text-sm transition-colors focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-900/10">
                </div>
                @error('jumlahPelunasan') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                @error('tanggalPelunasan') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                <input type="text" wire:model="keteranganPelunasan" placeholder="Keterangan (opsional)"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm transition-colors focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-900/10">
                <div class="flex gap-2">
                    <button type="submit" wire:loading.attr="disabled" wire:target="simpanPelunasan"
                        class="rounded-lg bg-emerald-700 px-3 py-2 text-sm font-medium text-white transition-colors hover:bg-emerald-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-700/30 disabled:opacity-50">
                        Catat Pelunasan
                    </button>
                    <button type="button" wire:click="batalMelunasi"
                        class="rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-700 transition-colors hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900/20">
                        Batal
                    </button>
                </div>
            </form>
        @else
            <button wire:click="melunasi({{ $hp->id }})" class="mt-2 text-sm text-emerald-700 hover:text-emerald-900 font-medium focus-visible:outline-none focus-visible:underline">
                Catat Pelunasan
            </button>
        @endif
    @endif
</div>
