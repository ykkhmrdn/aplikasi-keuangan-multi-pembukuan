{{--
    Badge kecil nama pembukuan, warna sesuai identitas (indigo/teal/violet). Kalau
    $pembukuan null, dianggap "Global" (dipakai Kategori buat kategori lintas pembukuan).

    Variabel yang diharapkan dari pemanggil:
    $pembukuan - model Pembukuan atau null
--}}
@php
    $badgeWarna = [
        'pribadi' => 'bg-blue-50 text-blue-700',
        'usaha' => 'bg-teal-50 text-teal-700',
        'kantor' => 'bg-violet-50 text-violet-700',
    ];
@endphp
<span class="inline-block rounded-full px-2.5 py-1 text-xs font-semibold {{ $pembukuan ? $badgeWarna[$pembukuan->tipe->value] : 'bg-slate-100 text-slate-600' }}">
    {{ $pembukuan->nama ?? 'Global' }}
</span>
