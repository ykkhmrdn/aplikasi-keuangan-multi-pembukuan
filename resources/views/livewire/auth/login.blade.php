<div>
    <div class="flex flex-col items-center mb-7">
        <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name') }}" class="w-14 h-14">
        <h1 class="text-xl font-bold text-slate-900 mt-3">Masuk</h1>
        <p class="text-sm text-slate-500 mt-0.5">{{ config('app.name') }}</p>
    </div>

    <form wire:submit="login">
        <div class="space-y-4">
            <div>
                <label for="username" class="block text-sm font-medium text-slate-700 mb-1.5">Username</label>
                <input
                    type="text"
                    id="username"
                    wire:model="username"
                    autofocus
                    autocomplete="username"
                    class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-slate-900 transition-colors focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-600/10"
                >
                @error('username')
                    <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div x-data="{ tampilkan: false }">
                <label for="password" class="block text-sm font-medium text-slate-700 mb-1.5">Password</label>
                <div class="relative">
                    <input
                        :type="tampilkan ? 'text' : 'password'"
                        id="password"
                        wire:model="password"
                        autocomplete="current-password"
                        class="w-full rounded-xl border border-slate-200 pl-3 pr-10 py-2.5 text-slate-900 transition-colors focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-600/10"
                    >
                    <button
                        type="button"
                        @click="tampilkan = !tampilkan"
                        class="absolute inset-y-0 right-0 flex items-center px-3 text-slate-400 hover:text-slate-600 focus-visible:outline-none focus-visible:text-slate-900"
                        :aria-label="tampilkan ? 'Sembunyikan password' : 'Tampilkan password'"
                    >
                        <svg x-show="!tampilkan" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.5 12S6 5 12 5s9.5 7 9.5 7-3.5 7-9.5 7-9.5-7-9.5-7z" />
                            <circle cx="12" cy="12" r="3" />
                        </svg>
                        <svg x-show="tampilkan" x-cloak viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18M10.6 10.6a3 3 0 004.24 4.24M9.4 5.3A9.8 9.8 0 0112 5c6 0 9.5 7 9.5 7a15.6 15.6 0 01-2.9 3.9M6.2 6.2C4 7.7 2.5 10 2.5 10s3.5 7 9.5 7a9.6 9.6 0 004-1" />
                        </svg>
                    </button>
                </div>
                @error('password')
                    <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <label class="mt-5 flex items-center gap-2 text-sm text-slate-600 cursor-pointer">
            <input type="checkbox" wire:model="remember" class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-2 focus:ring-blue-600/20 focus:ring-offset-0">
            Ingat saya
        </label>

        <button
            type="submit"
            wire:loading.attr="disabled"
            wire:target="login"
            class="mt-7 w-full rounded-lg bg-blue-600 px-4 py-3 text-white font-semibold transition-colors hover:bg-blue-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600/40 disabled:opacity-50 motion-safe:active:scale-[0.98]"
        >
            <span wire:loading.remove wire:target="login">Masuk</span>
            <span wire:loading wire:target="login">Memproses...</span>
        </button>
    </form>
</div>
