<?php

namespace App\Livewire\HutangPiutang;

use App\Enums\StatusHutangPiutang;
use App\Models\HutangPiutang;
use App\Models\PelunasanHutang;
use App\Models\Pembukuan;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Index extends Component
{
    public Pembukuan $pembukuan;

    // state form catat bon baru
    public bool $showForm = false;

    public string $dariPembukuanId = '';

    public string $kePembukuanId = '';

    public string $jumlah = '';

    public string $tanggal = '';

    public string $keterangan = '';

    // state form pelunasan
    public ?int $melunasiId = null;

    public string $jumlahPelunasan = '';

    public string $tanggalPelunasan = '';

    public string $keteranganPelunasan = '';

    public function render()
    {
        return view('livewire.hutang-piutang.index', [
            'piutangList' => $this->pembukuan->hutangDiberikan()
                ->with(['kePembukuan', 'pelunasan'])
                ->orderByDesc('tanggal')
                ->orderByDesc('id')
                ->get(),
            'hutangList' => $this->pembukuan->hutangDiterima()
                ->with(['dariPembukuan', 'pelunasan'])
                ->orderByDesc('tanggal')
                ->orderByDesc('id')
                ->get(),
            'pembukuanList' => Pembukuan::orderBy('id')->get(),
            'piutangOutstanding' => $this->pembukuan->piutangOutstanding(),
            'hutangOutstanding' => $this->pembukuan->hutangOutstanding(),
        ]);
    }

    public function tambah(): void
    {
        $this->resetForm();
        $this->dariPembukuanId = (string) $this->pembukuan->id;
        $this->showForm = true;
    }

    public function batal(): void
    {
        $this->resetForm();
        $this->showForm = false;
    }

    public function simpan(): void
    {
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
            'jumlah' => ['required', 'numeric', 'min:0.01'],
            'tanggal' => ['required', 'date'],
            'keterangan' => ['nullable', 'string', 'max:1000'],
        ], [], [
            'dariPembukuanId' => 'pembukuan pemberi',
            'kePembukuanId' => 'pembukuan penerima',
            'jumlah' => 'jumlah',
            'tanggal' => 'tanggal',
            'keterangan' => 'keterangan',
        ]);

        HutangPiutang::create([
            'dari_pembukuan_id' => $this->dariPembukuanId,
            'ke_pembukuan_id' => $this->kePembukuanId,
            'jumlah' => $this->jumlah,
            'tanggal' => $this->tanggal,
            'keterangan' => $this->keterangan !== '' ? $this->keterangan : null,
        ]);

        $this->resetForm();
        $this->showForm = false;
    }

    public function melunasi(int $id): void
    {
        $hutangPiutang = HutangPiutang::findOrFail($id);

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
                    $hutangPiutang = HutangPiutang::find($this->melunasiId);
                    $sisa = $hutangPiutang?->sisaOutstanding() ?? '0.00';

                    if (bccomp((string) $value, $sisa, 2) > 0) {
                        $fail('Jumlah pelunasan melebihi sisa outstanding (Rp'.number_format($sisa, 0, ',', '.').').');
                    }
                },
            ],
            'tanggalPelunasan' => ['required', 'date'],
            'keteranganPelunasan' => ['nullable', 'string', 'max:1000'],
        ], [], [
            'jumlahPelunasan' => 'jumlah pelunasan',
            'tanggalPelunasan' => 'tanggal',
            'keteranganPelunasan' => 'keterangan',
        ]);

        try {
            DB::transaction(function () {
                // row lock supaya tidak race kalau ada dua pelunasan diproses bersamaan
                $hutangPiutang = HutangPiutang::whereKey($this->melunasiId)->lockForUpdate()->firstOrFail();
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

    private function resetForm(): void
    {
        $this->reset(['dariPembukuanId', 'kePembukuanId', 'jumlah', 'keterangan']);
        $this->tanggal = now()->format('Y-m-d');
    }
}
