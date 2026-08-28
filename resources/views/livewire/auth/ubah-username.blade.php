<div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm space-y-4 max-w-sm">
    <div>
        <h2 class="text-sm font-semibold text-slate-900">Ubah Username</h2>
        <p class="text-xs text-slate-500 mt-0.5">Dipakai untuk login, harus unik.</p>
    </div>

    @if ($berhasil)
        <div class="rounded-lg bg-emerald-50 border border-emerald-200 px-3 py-2 text-sm text-emerald-700">
            Username berhasil diubah.
        </div>
    @endif

    <form wire:submit="simpan" class="space-y-4">
        <div>
            <label for="username" class="block text-sm font-medium text-slate-700 mb-1.5">Username</label>
            <input
                type="text"
                id="username"
                wire:model="username"
                autocomplete="username"
                class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-slate-900 transition-colors focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-600/10"
            >
            @error('username') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <button
            type="submit"
            wire:loading.attr="disabled"
            wire:target="simpan"
            class="w-full rounded-lg bg-blue-600 px-4 py-2.5 text-white font-semibold transition-colors hover:bg-blue-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600/30 disabled:opacity-50 motion-safe:active:scale-[0.98]"
        >
            <span wire:loading.remove wire:target="simpan">Simpan Username</span>
            <span wire:loading wire:target="simpan">Memproses...</span>
        </button>
    </form>
</div>
