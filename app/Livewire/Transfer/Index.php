<?php

namespace App\Livewire\Transfer;

use App\Models\Pembukuan;
use App\Models\TransferSaldo;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Index extends Component
{
    // state form
    public bool $showForm = false;

    public string $dariPembukuanId = '';

    public string $kePembukuanId = '';

    public string $jumlah = '';

    public string $tanggal = '';

    public string $keterangan = '';

    public function render()
    {
        return view('livewire.transfer.index', [
            'transferList' => TransferSaldo::query()
                ->with(['dariPembukuan', 'kePembukuan'])
                ->orderByDesc('tanggal')
                ->orderByDesc('id')
                ->get(),
            'pembukuanList' => Pembukuan::orderBy('id')->get(),
        ]);
    }

    public function tambah(): void
    {
        $this->resetForm();
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
                        $fail('Pembukuan tujuan harus beda dengan pembukuan asal.');
                    }
                },
            ],
            'jumlah' => ['required', 'numeric', 'min:0.01'],
            'tanggal' => ['required', 'date'],
            'keterangan' => ['nullable', 'string', 'max:1000'],
        ], [], [
            'dariPembukuanId' => 'pembukuan asal',
            'kePembukuanId' => 'pembukuan tujuan',
            'jumlah' => 'jumlah',
            'tanggal' => 'tanggal',
            'keterangan' => 'keterangan',
        ]);

        // satu insert saja, tapi tetap dibungkus transaction sesuai aturan kerja
        // (konsisten dengan pelunasan hutang, jaga-jaga kalau nanti ada penulisan terkait lain)
        DB::transaction(function () {
            TransferSaldo::create([
                'dari_pembukuan_id' => $this->dariPembukuanId,
                'ke_pembukuan_id' => $this->kePembukuanId,
                'jumlah' => $this->jumlah,
                'tanggal' => $this->tanggal,
                'keterangan' => $this->keterangan !== '' ? $this->keterangan : null,
            ]);
        });

        $this->resetForm();
        $this->showForm = false;
    }

    private function resetForm(): void
    {
        $this->reset(['dariPembukuanId', 'kePembukuanId', 'jumlah', 'keterangan']);
        $this->tanggal = now()->format('Y-m-d');
    }
}
