<?php

namespace App\Livewire\Transaksi;

use App\Enums\TipeTransaksi;
use App\Models\Kategori;
use App\Models\Pembukuan;
use App\Models\Transaksi;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination;

    public Pembukuan $pembukuan;

    // filter, pencarian, & urutan tampilan
    public string $filterKategori = 'semua';

    public string $filterDari = '';

    public string $filterSampai = '';

    public string $search = '';

    public string $sort = 'tanggal_terbaru';

    // state form tambah/edit
    public bool $showForm = false;

    public ?int $editingId = null;

    public string $tipe = 'pemasukan';

    public string $kategoriId = '';

    public string $jumlah = '';

    public string $tanggal = '';

    public string $keterangan = '';

    // state konfirmasi hapus
    public ?int $confirmingDeleteId = null;

    public function updatedTipe(): void
    {
        // kategori yang kepilih sebelumnya mungkin tidak cocok lagi dengan tipe baru
        $this->kategoriId = '';
    }

    public function updatedFilterKategori(): void
    {
        $this->resetPage();
    }

    public function updatedFilterDari(): void
    {
        $this->resetPage();
    }

    public function updatedFilterSampai(): void
    {
        $this->resetPage();
    }

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
        $query = Transaksi::query()
            ->where('pembukuan_id', $this->pembukuan->id)
            ->with('kategori');

        if ($this->filterKategori !== 'semua') {
            $query->where('kategori_id', $this->filterKategori);
        }

        if ($this->filterDari) {
            $query->whereDate('tanggal', '>=', $this->filterDari);
        }

        if ($this->filterSampai) {
            $query->whereDate('tanggal', '<=', $this->filterSampai);
        }

        if ($this->search !== '') {
            $query->where(function ($q) {
                $q->where('keterangan', 'like', '%'.$this->search.'%')
                    ->orWhereHas('kategori', function ($kategoriQuery) {
                        $kategoriQuery->where('nama', 'like', '%'.$this->search.'%');
                    });
            });
        }

        match ($this->sort) {
            'tanggal_terlama' => $query->orderBy('tanggal')->orderBy('id'),
            'jumlah_terbesar' => $query->orderByDesc('jumlah'),
            'jumlah_terkecil' => $query->orderBy('jumlah'),
            default => $query->orderByDesc('tanggal')->orderByDesc('id'), // tanggal_terbaru
        };

        return view('livewire.transaksi.index', [
            'transaksiList' => $query->paginate(10),
            'kategoriSemua' => $this->kategoriUntukPembukuan(),
            'tipeOptions' => TipeTransaksi::cases(),
        ]);
    }

    /** Kategori yang berlaku untuk pembukuan ini: global + khusus pembukuan ini. */
    private function kategoriUntukPembukuan(): Collection
    {
        return Kategori::query()
            ->where(function ($query) {
                $query->whereNull('pembukuan_id')->orWhere('pembukuan_id', $this->pembukuan->id);
            })
            ->orderBy('tipe')
            ->orderBy('nama')
            ->get();
    }

    public function tambah(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $transaksi = Transaksi::where('pembukuan_id', $this->pembukuan->id)->findOrFail($id);

        $this->editingId = $transaksi->id;
        $this->tipe = $transaksi->tipe->value;
        $this->kategoriId = (string) $transaksi->kategori_id;
        $this->jumlah = (string) $transaksi->jumlah;
        $this->tanggal = $transaksi->tanggal->format('Y-m-d');
        $this->keterangan = (string) $transaksi->keterangan;
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
            'tipe' => ['required', Rule::enum(TipeTransaksi::class)],
            'kategoriId' => [
                'required',
                function ($attribute, $value, $fail) {
                    $valid = Kategori::query()
                        ->whereKey($value)
                        ->where('tipe', $this->tipe)
                        ->where(function ($query) {
                            $query->whereNull('pembukuan_id')->orWhere('pembukuan_id', $this->pembukuan->id);
                        })
                        ->exists();

                    if (! $valid) {
                        $fail('Kategori tidak valid untuk tipe dan pembukuan ini.');
                    }
                },
            ],
            'jumlah' => ['required', 'numeric', 'min:0.01'],
            'tanggal' => ['required', 'date'],
            'keterangan' => ['nullable', 'string', 'max:1000'],
        ], [], [
            'tipe' => 'tipe',
            'kategoriId' => 'kategori',
            'jumlah' => 'jumlah',
            'tanggal' => 'tanggal',
            'keterangan' => 'keterangan',
        ]);

        $data = [
            'pembukuan_id' => $this->pembukuan->id,
            'kategori_id' => $this->kategoriId,
            'tipe' => $this->tipe,
            'jumlah' => $this->jumlah,
            'tanggal' => $this->tanggal,
            'keterangan' => $this->keterangan !== '' ? $this->keterangan : null,
        ];

        if ($this->editingId) {
            Transaksi::where('pembukuan_id', $this->pembukuan->id)
                ->findOrFail($this->editingId)
                ->update($data);
        } else {
            Transaksi::create($data);
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

    public function hapus(int $id): void
    {
        Transaksi::where('pembukuan_id', $this->pembukuan->id)->findOrFail($id)->delete();
        $this->confirmingDeleteId = null;
    }

    private function resetForm(): void
    {
        $this->reset(['editingId', 'kategoriId', 'jumlah', 'keterangan']);
        $this->tipe = 'pemasukan';
        $this->tanggal = now()->format('Y-m-d');
    }
}
