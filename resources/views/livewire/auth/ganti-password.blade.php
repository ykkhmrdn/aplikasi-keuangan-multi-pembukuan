<div class="space-y-6">
    <h1 class="text-xl sm:text-2xl font-semibold tracking-tight text-slate-900">Akun</h1>

    <livewire:auth.ubah-username />

    @if ($berhasil)
        <div class="max-w-sm rounded-xl bg-emerald-50 border border-emerald-200 px-3 py-2.5 text-sm text-emerald-700">
            Password berhasil diubah.
        </div>
    @endif

    <form wire:submit="simpan" class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm space-y-4 max-w-sm">
        <h2 class="text-sm font-semibold text-slate-900">Ganti Password</h2>

        @php
            $inputClass = 'w-full rounded-lg border border-slate-300 pl-3 pr-10 py-2.5 text-slate-900 transition-colors focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-900/10';
        @endphp

        <div x-data="{ tampilkan: false }">
            <label for="password_lama" class="block text-sm font-medium text-slate-700 mb-1.5">Password Lama</label>
            <div class="relative">
                <input
                    :type="tampilkan ? 'text' : 'password'"
                    id="password_lama"
                    wire:model="password_lama"
                    autocomplete="current-password"
                    class="{{ $inputClass }}"
                >
                <button type="button" @click="tampilkan = !tampilkan" class="absolute inset-y-0 right-0 flex items-center px-3 text-slate-400 hover:text-slate-600 focus-visible:outline-none focus-visible:text-slate-900" :aria-label="tampilkan ? 'Sembunyikan password' : 'Tampilkan password'">
                    <svg x-show="!tampilkan" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.5 12S6 5 12 5s9.5 7 9.5 7-3.5 7-9.5 7-9.5-7-9.5-7z" />
                        <circle cx="12" cy="12" r="3" />
                    </svg>
                    <svg x-show="tampilkan" x-cloak viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18M10.6 10.6a3 3 0 004.24 4.24M9.4 5.3A9.8 9.8 0 0112 5c6 0 9.5 7 9.5 7a15.6 15.6 0 01-2.9 3.9M6.2 6.2C4 7.7 2.5 10 2.5 10s3.5 7 9.5 7a9.6 9.6 0 004-1" />
                    </svg>
                </button>
            </div>
            @error('password_lama') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div x-data="{ tampilkan: false }">
            <label for="password_baru" class="block text-sm font-medium text-slate-700 mb-1.5">Password Baru</label>
            <div class="relative">
                <input
                    :type="tampilkan ? 'text' : 'password'"
                    id="password_baru"
                    wire:model="password_baru"
                    autocomplete="new-password"
                    class="{{ $inputClass }}"
                >
                <button type="button" @click="tampilkan = !tampilkan" class="absolute inset-y-0 right-0 flex items-center px-3 text-slate-400 hover:text-slate-600 focus-visible:outline-none focus-visible:text-slate-900" :aria-label="tampilkan ? 'Sembunyikan password' : 'Tampilkan password'">
                    <svg x-show="!tampilkan" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.5 12S6 5 12 5s9.5 7 9.5 7-3.5 7-9.5 7-9.5-7-9.5-7z" />
                        <circle cx="12" cy="12" r="3" />
                    </svg>
                    <svg x-show="tampilkan" x-cloak viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18M10.6 10.6a3 3 0 004.24 4.24M9.4 5.3A9.8 9.8 0 0112 5c6 0 9.5 7 9.5 7a15.6 15.6 0 01-2.9 3.9M6.2 6.2C4 7.7 2.5 10 2.5 10s3.5 7 9.5 7a9.6 9.6 0 004-1" />
                    </svg>
                </button>
            </div>
            <p class="mt-1.5 text-xs text-slate-500">Minimal 8 karakter.</p>
            @error('password_baru') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div x-data="{ tampilkan: false }">
            <label for="password_baru_confirmation" class="block text-sm font-medium text-slate-700 mb-1.5">Ulangi Password Baru</label>
            <div class="relative">
                <input
                    :type="tampilkan ? 'text' : 'password'"
                    id="password_baru_confirmation"
                    wire:model="password_baru_confirmation"
                    autocomplete="new-password"
                    class="{{ $inputClass }}"
                >
                <button type="button" @click="tampilkan = !tampilkan" class="absolute inset-y-0 right-0 flex items-center px-3 text-slate-400 hover:text-slate-600 focus-visible:outline-none focus-visible:text-slate-900" :aria-label="tampilkan ? 'Sembunyikan password' : 'Tampilkan password'">
                    <svg x-show="!tampilkan" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.5 12S6 5 12 5s9.5 7 9.5 7-3.5 7-9.5 7-9.5-7-9.5-7z" />
                        <circle cx="12" cy="12" r="3" />
                    </svg>
                    <svg x-show="tampilkan" x-cloak viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18M10.6 10.6a3 3 0 004.24 4.24M9.4 5.3A9.8 9.8 0 0112 5c6 0 9.5 7 9.5 7a15.6 15.6 0 01-2.9 3.9M6.2 6.2C4 7.7 2.5 10 2.5 10s3.5 7 9.5 7a9.6 9.6 0 004-1" />
                    </svg>
                </button>
            </div>
        </div>

        <button
            type="submit"
            wire:loading.attr="disabled"
            wire:target="simpan"
            class="w-full rounded-lg bg-slate-900 px-4 py-2.5 text-white font-medium transition-colors hover:bg-slate-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900/30 disabled:opacity-50 motion-safe:active:scale-[0.98]"
        >
            <span wire:loading.remove wire:target="simpan">Simpan Password Baru</span>
            <span wire:loading wire:target="simpan">Memproses...</span>
        </button>
    </form>
</div>
