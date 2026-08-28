<?php

namespace App\Livewire\HutangPiutang;

use App\Enums\StatusHutangPiutang;
use App\Models\HutangPiutang;
use App\Models\PelunasanHutang;
use App\Models\Pembukuan;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination;

    public Pembukuan $pembukuan;

    // pencarian & urutan tampilan
    public string $search = '';

    public string $sort = 'tanggal_terbaru';

    // state form catat/edit bon
    public bool $showForm = false;

    public ?int $editingId = null;

    public string $dariPembukuanId = '';

    public string $kePembukuanId = '';

    public string $jumlah = '';

    public string $tanggal = '';

    public string $keterangan = '';

    // state hapus
    public ?int $confirmingDeleteId = null;

    // state form pelunasan
    public ?int $melunasiId = null;

    public string $jumlahPelunasan = '';

    public string $tanggalPelunasan = '';

    public string $keteranganPelunasan = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedSort(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        // hanya bon yang DITERIMA pembukuan ini (hutang) yang ditampilkan - sisi
        // "piutang" sengaja dihilangkan dari tampilan sesuai permintaan client
        // (meeting 28 Agt 2026), tapi bon yang sama tetap muncul normal di halaman
        // pembukuan lawan (yang menerima bon dari pembukuan ini)
        $hutangQuery = $this->pembukuan->hutangDiterima()->with(['dariPembukuan', 'pelunasan']);

        if ($this->search !== '') {
            $hutangQuery->where(function ($q) {
                $q->where('keterangan', 'like', '%'.$this->search.'%')
                    ->orWhereHas('dariPembukuan', function ($pembukuanQuery) {
                        $pembukuanQuery->where('nama', 'like', '%'.$this->search.'%');
                    });
            });
        }

        match ($this->sort) {
            'tanggal_terlama' => $hutangQuery->orderBy('tanggal')->orderBy('id'),
            'jumlah_terbesar' => $hutangQuery->orderByDesc('jumlah'),
            'jumlah_terkecil' => $hutangQuery->orderBy('jumlah'),
            default => $hutangQuery->orderByDesc('tanggal')->orderByDesc('id'), // tanggal_terbaru
        };

        return view('livewire.hutang-piutang.index', [
            'hutangList' => $hutangQuery->paginate(10),
            'pembukuanList' => Pembukuan::orderBy('id')->get(),
            'hutangOutstanding' => $this->pembukuan->hutangOutstanding(),
        ]);
    }

    public function tambah(): void
    {
        $this->resetForm();
        $this->dariPembukuanId = (string) $this->pembukuan->id;
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $hutangPiutang = $this->hutangPiutangScoped()->findOrFail($id);

        $this->editingId = $hutangPiutang->id;
        $this->dariPembukuanId = (string) $hutangPiutang->dari_pembukuan_id;
        $this->kePembukuanId = (string) $hutangPiutang->ke_pembukuan_id;
        $this->jumlah = (string) $hutangPiutang->jumlah;
        $this->tanggal = $hutangPiutang->tanggal->format('Y-m-d');
        $this->keterangan = (string) $hutangPiutang->keterangan;
        $this->showForm = true;
    }

    public function batal(): void
    {
        $this->resetForm();
        $this->showForm = false;
    }

    public function simpan(): void
    {
        // kalau lagi edit bon yang sudah ada pelunasan, jumlah gak boleh diedit
        // jadi lebih kecil dari total yang udah dibayar (integritas data)
        $totalPelunasanSaatIni = $this->editingId
            ? (string) PelunasanHutang::where('hutang_piutang_id', $this->editingId)->sum('jumlah')
            : '0.00';

        $this->validate([
            'dariPembukuanId' => ['required', 'exists:pembukuan,id'],
            'kePembukuanId' => [
                'required',
                'exists:pembukuan,id',
                function ($attribute, $value, $fail) {
                    if ($value === $this->dariPembukuanId) {
                        $fail('Pembukuan penerima harus beda dengan pembukuan pemberi.');
                    }
                },
            ],
            'jumlah' => [
                'required', 'numeric', 'min:0.01',
                function ($attribute, $value, $fail) use ($totalPelunasanSaatIni) {
                    if ($this->editingId && bccomp((string) $value, $totalPelunasanSaatIni, 2) < 0) {
                        $fail('Jumlah tidak boleh kurang dari total pelunasan yang sudah dicatat (Rp'.number_format($totalPelunasanSaatIni, 0, ',', '.').').');
                    }
                },
            ],
            // batas 10 tahun ke belakang & 1 tahun ke depan, cuma nangkep typo tahun,
            // bukan larangan backdate wajar (lihat alasan lengkap di docs/DECISION_LOG.md)
            'tanggal' => ['required', 'date', 'after_or_equal:'.now()->subYears(10)->format('Y-m-d'), 'before_or_equal:'.now()->addYear()->format('Y-m-d')],
            'keterangan' => ['nullable', 'string', 'max:1000'],
        ], [], [
            'dariPembukuanId' => 'pembukuan pemberi',
            'kePembukuanId' => 'pembukuan penerima',
            'jumlah' => 'jumlah',
            'tanggal' => 'tanggal',
            'keterangan' => 'keterangan',
        ]);

        $data = [
            'dari_pembukuan_id' => $this->dariPembukuanId,
            'ke_pembukuan_id' => $this->kePembukuanId,
            'jumlah' => $this->jumlah,
            'tanggal' => $this->tanggal,
            'keterangan' => $this->keterangan !== '' ? $this->keterangan : null,
        ];

        if ($this->editingId) {
            $hutangPiutang = $this->hutangPiutangScoped()->findOrFail($this->editingId);
            $hutangPiutang->update($data);
            $this->sinkronStatus($hutangPiutang);
        } else {
            HutangPiutang::create($data);
        }

        $this->resetForm();
        $this->showForm = false;
    }

    public function confirmHapus(int $id): void
    {
        $this->confirmingDeleteId = $id;
    }

    public function batalHapus(): void
    {
        $this->confirmingDeleteId = null;
    }

    /**
     * Hapus selalu boleh, termasuk bon yang sudah ada pelunasannya (keputusan
     * client, lihat docs/DECISION_LOG.md). "Refund" saldo otomatis tanpa logic
     * tambahan - Pembukuan::saldo() dihitung dinamis tiap render, dan baris
     * pelunasan_hutang ikut kehapus lewat cascadeOnDelete di migration.
     */
    public function hapus(int $id): void
    {
        $this->hutangPiutangScoped()->findOrFail($id)->delete();
        $this->confirmingDeleteId = null;
    }

    public function melunasi(int $id): void
    {
        // scoped ke pembukuan yang sedang dibuka (dari sisi manapun, berpiutang/berutang),
        // konsisten sama pola anti akses-silang di Transaksi - cegah pelunasan bon pembukuan
        // lain lewat manipulasi request walau di UI tombolnya memang tidak pernah muncul
        $hutangPiutang = $this->hutangPiutangScoped()->findOrFail($id);

        $this->melunasiId = $id;
        $this->jumlahPelunasan = $hutangPiutang->sisaOutstanding();
        $this->tanggalPelunasan = now()->format('Y-m-d');
        $this->keteranganPelunasan = '';
    }

    public function batalMelunasi(): void
    {
        $this->reset(['melunasiId', 'jumlahPelunasan', 'tanggalPelunasan', 'keteranganPelunasan']);
    }

    public function simpanPelunasan(): void
    {
        $this->validate([
            'jumlahPelunasan' => [
                'required', 'numeric', 'min:0.01',
                function ($attribute, $value, $fail) {
                    $hutangPiutang = $this->hutangPiutangScoped()->find($this->melunasiId);
                    $sisa = $hutangPiutang?->sisaOutstanding() ?? '0.00';

                    if (bccomp((string) $value, $sisa, 2) > 0) {
                        $fail('Jumlah pelunasan melebihi sisa outstanding (Rp'.number_format($sisa, 0, ',', '.').').');
                    }
                },
            ],
            // batas 10 tahun ke belakang & 1 tahun ke depan, cuma nangkep typo tahun
            'tanggalPelunasan' => ['required', 'date', 'after_or_equal:'.now()->subYears(10)->format('Y-m-d'), 'before_or_equal:'.now()->addYear()->format('Y-m-d')],
            'keteranganPelunasan' => ['nullable', 'string', 'max:1000'],
        ], [], [
            'jumlahPelunasan' => 'jumlah pelunasan',
            'tanggalPelunasan' => 'tanggal',
            'keteranganPelunasan' => 'keterangan',
        ]);

        try {
            DB::transaction(function () {
                // row lock supaya tidak race kalau ada dua pelunasan diproses bersamaan
                $hutangPiutang = $this->hutangPiutangScoped()->whereKey($this->melunasiId)->lockForUpdate()->firstOrFail();
                $sisaSaatIni = $hutangPiutang->sisaOutstanding();

                if (bccomp($this->jumlahPelunasan, $sisaSaatIni, 2) > 0) {
                    throw new \RuntimeException('Jumlah pelunasan melebihi sisa outstanding.');
                }

                PelunasanHutang::create([
                    'hutang_piutang_id' => $hutangPiutang->id,
                    'jumlah' => $this->jumlahPelunasan,
                    'tanggal' => $this->tanggalPelunasan,
                    'keterangan' => $this->keteranganPelunasan !== '' ? $this->keteranganPelunasan : null,
                ]);

                $sisaBaru = bcsub($sisaSaatIni, $this->jumlahPelunasan, 2);

                if (bccomp($sisaBaru, '0', 2) <= 0) {
                    $hutangPiutang->update([
                        'status' => StatusHutangPiutang::Lunas,
                        'tanggal_lunas' => $this->tanggalPelunasan,
                    ]);
                }
            });
        } catch (\RuntimeException $e) {
            $this->addError('jumlahPelunasan', $e->getMessage());

            return;
        }

        $this->batalMelunasi();
    }

    /** Hitung ulang status lunas/belum berdasarkan sisa outstanding terbaru - dipanggil setelah edit jumlah bon. */
    private function sinkronStatus(HutangPiutang $hutangPiutang): void
    {
        $hutangPiutang->refresh();
        $sisa = $hutangPiutang->sisaOutstanding();

        if (bccomp($sisa, '0', 2) <= 0) {
            $hutangPiutang->update([
                'status' => StatusHutangPiutang::Lunas,
                'tanggal_lunas' => $hutangPiutang->tanggal_lunas?->format('Y-m-d') ?? now()->format('Y-m-d'),
            ]);
        } else {
            $hutangPiutang->update(['status' => StatusHutangPiutang::BelumLunas, 'tanggal_lunas' => null]);
        }
    }

    private function resetForm(): void
    {
        $this->reset(['editingId', 'dariPembukuanId', 'kePembukuanId', 'jumlah', 'keterangan']);
        $this->tanggal = now()->format('Y-m-d');
    }

    /** Bon yang melibatkan pembukuan yang sedang dibuka (dari sisi manapun). */
    private function hutangPiutangScoped()
    {
        return HutangPiutang::where(function ($query) {
            $query->where('dari_pembukuan_id', $this->pembukuan->id)
                ->orWhere('ke_pembukuan_id', $this->pembukuan->id);
        });
    }
}
