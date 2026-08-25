<div class="w-full max-w-sm">
    <h1 class="text-xl font-semibold text-slate-900 mb-6 text-center">Masuk</h1>

    <form wire:submit="login" class="space-y-4">
        <div>
            <label for="username" class="block text-sm font-medium text-slate-700 mb-1">Username</label>
            <input
                type="text"
                id="username"
                wire:model="username"
                autofocus
                autocomplete="username"
                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500"
            >
            @error('username')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-slate-700 mb-1">Password</label>
            <input
                type="password"
                id="password"
                wire:model="password"
                autocomplete="current-password"
                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500"
            >
            @error('password')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <label class="flex items-center gap-2 text-sm text-slate-600">
            <input type="checkbox" wire:model="remember" class="rounded border-slate-300 text-slate-700 focus:ring-slate-500">
            Ingat saya
        </label>

        <button
            type="submit"
            wire:loading.attr="disabled"
            wire:target="login"
            class="w-full rounded-lg bg-slate-900 px-4 py-2 text-white font-medium hover:bg-slate-800 disabled:opacity-50"
        >
            <span wire:loading.remove wire:target="login">Masuk</span>
            <span wire:loading wire:target="login">Memproses...</span>
        </button>
    </form>
</div>
