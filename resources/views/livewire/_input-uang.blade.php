{{--
    Input angka rupiah dengan live-format pemisah ribuan (mis. ketik "5000000" langsung
    kelihatan "5.000.000" sambil ngetik), pakai $wire.entangle (bukan @entangle yang
    deprecated di Livewire 4.x). Property Livewire yang di-entangle tetap kesimpan angka
    polos tanpa titik pemisah, jadi validasi numeric/min:0.01 di server gak perlu berubah.
    Ketik langsung dari kosong, gak perlu hapus placeholder dulu.

    Variabel yang diharapkan dari pemanggil:
    $field       - nama property Livewire yang di-bind, mis. "jumlah"
    $errorField  - (opsional) nama field buat cek @error, default sama dengan $field
                   (dipakai kalau nama property beda dari nama field validasi, jarang kepakai)
--}}
@php($errorField = $errorField ?? $field)
<div
    x-data="{
        raw: $wire.entangle('{{ $field }}'),
        get display() {
            const angka = parseFloat(this.raw || 0);
            return angka ? new Intl.NumberFormat('id-ID').format(angka) : '';
        },
    }"
>
    <input
        type="text"
        inputmode="numeric"
        autocomplete="off"
        :value="display"
        @input="raw = $event.target.value.replace(/[^0-9]/g, '')"
        placeholder="0"
        class="w-full rounded-lg border px-3 py-2.5 text-slate-900 transition-colors focus:outline-none focus:ring-2
            {{ $errors->has($errorField) ? 'border-red-400 focus:border-red-500 focus:ring-red-500/10' : 'border-slate-200 focus:border-blue-400 focus:ring-blue-600/10' }}"
    >
</div>
