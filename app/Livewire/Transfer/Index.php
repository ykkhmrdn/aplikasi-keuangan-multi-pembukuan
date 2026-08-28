<?php

namespace App\Livewire\Transfer;

use App\Models\Pembukuan;
use App\Models\TransferSaldo;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination;

    // state form
    public bool $showForm = false;

    public ?int $editingId = null;

    public string $dariPembukuanId = '';

    public string $kePembukuanId = '';

    public string $jumlah = '';

    public string $tanggal = '';

    public string $keterangan = '';

    // state hapus
    public ?int $confirmingDeleteId = null;

    // pencarian & urutan tampilan
    public string $search = '';

    public string $sort = 'tanggal_terbaru';

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
        $query = TransferSaldo::query()->with(['dariPembukuan', 'kePembukuan']);

        if ($this->search !== '') {
            $query->where(function ($q) {
                $q->where('keterangan', 'like', '%'.$this->search.'%')
                    ->orWhereHas('dariPembukuan', function ($pembukuanQuery) {
                        $pembukuanQuery->where('nama', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('kePembukuan', function ($pembukuanQuery) {
                        $pembukuanQuery->where('nama', 'like', '%'.$this->search.'%');
                    });
            });
        }

        match ($this->sort) {
            'tanggal_terlama' => $query->orderBy('tanggal')->orderBy('id'),
            'jumlah_terbesar' => $query->orderByDesc('jumlah'),
            'jumlah_terkecil' => $query->orderBy('jumlah'),
            default => $query->orderByDesc('tanggal')->orderByDesc('id'), // tanggal_terbaru
        };

        return view('livewire.transfer.index', [
            'transferList' => $query->paginate(10),
            'pembukuanList' => Pembukuan::orderBy('id')->get(),
        ]);
    }

    public function tambah(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $transfer = TransferSaldo::findOrFail($id);

        $this->editingId = $transfer->id;
        $this->dariPembukuanId = (string) $transfer->dari_pembukuan_id;
        $this->kePembukuanId = (string) $transfer->ke_pembukuan_id;
        $this->jumlah = (string) $transfer->jumlah;
        $this->tanggal = $transfer->tanggal->format('Y-m-d');
        $this->keterangan = (string) $transfer->keterangan;
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
            // batas 10 tahun ke belakang & 1 tahun ke depan, cuma nangkep typo tahun,
            // bukan larangan backdate (lihat alasan lengkap di docs/DECISION_LOG.md)
            'tanggal' => ['required', 'date', 'after_or_equal:'.now()->subYears(10)->format('Y-m-d'), 'before_or_equal:'.now()->addYear()->format('Y-m-d')],
            'keterangan' => ['nullable', 'string', 'max:1000'],
        ], [], [
            'dariPembukuanId' => 'pembukuan asal',
            'kePembukuanId' => 'pembukuan tujuan',
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

        // satu write saja, tapi tetap dibungkus transaction sesuai aturan kerja
        // (konsisten dengan pelunasan hutang, jaga-jaga kalau nanti ada penulisan terkait lain)
        DB::transaction(function () use ($data) {
            if ($this->editingId) {
                TransferSaldo::findOrFail($this->editingId)->update($data);
            } else {
                TransferSaldo::create($data);
            }
        });

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
     * Hapus selalu boleh - saldo kedua pembukuan otomatis "kembali normal" tanpa
     * logic tambahan, karena Pembukuan::saldo() dihitung dinamis tiap render
     * (bukan kolom cache), jadi transfer yang kehapus otomatis gak lagi kehitung.
     */
    public function hapus(int $id): void
    {
        TransferSaldo::findOrFail($id)->delete();
        $this->confirmingDeleteId = null;
    }

    private function resetForm(): void
    {
        $this->reset(['editingId', 'dariPembukuanId', 'kePembukuanId', 'jumlah', 'keterangan']);
        $this->tanggal = now()->format('Y-m-d');
    }
}
