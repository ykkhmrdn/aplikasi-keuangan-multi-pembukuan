<?php

namespace App\Livewire\Kategori;

use App\Enums\TipeTransaksi;
use App\Models\Kategori;
use App\Models\Pembukuan;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination;

    // filter, pencarian, & urutan tampilan
    public string $filterTipe = 'semua';

    public string $filterPembukuan = 'semua';

    public string $search = '';

    public string $sort = 'nama_asc';

    // state form tambah/edit
    public bool $showForm = false;

    public ?int $editingId = null;

    public bool $editingLocked = false;

    public string $nama = '';

    public ?string $tipe = null;

    public string $pembukuanId = 'global';

    // state konfirmasi hapus
    public ?int $confirmingDeleteId = null;

    public ?string $deleteErrorMessage = null;

    public function updatedFilterTipe(): void
    {
        $this->resetPage();
    }

    public function updatedFilterPembukuan(): void
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
        $query = Kategori::query()->with('pembukuan');

        if ($this->filterTipe !== 'semua') {
            $query->where('tipe', $this->filterTipe);
        }

        if ($this->filterPembukuan === 'global') {
            $query->whereNull('pembukuan_id');
        } elseif ($this->filterPembukuan !== 'semua') {
            $query->where('pembukuan_id', $this->filterPembukuan);
        }

        if ($this->search !== '') {
            $query->where('nama', 'like', '%'.$this->search.'%');
        }

        match ($this->sort) {
            'nama_desc' => $query->orderByDesc('nama'),
            'terbaru' => $query->orderByDesc('created_at'),
            'terlama' => $query->orderBy('created_at'),
            default => $query->orderBy('tipe')->orderBy('nama'), // nama_asc, default: dikelompokkan per tipe biar scannable
        };

        return view('livewire.kategori.index', [
            'kategoriList' => $query->paginate(10),
            'pembukuanList' => Pembukuan::orderBy('id')->get(),
            'tipeOptions' => TipeTransaksi::cases(),
        ]);
    }

    public function tambah(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $kategori = Kategori::findOrFail($id);

        $this->editingId = $kategori->id;
        $this->editingLocked = $kategori->sudahDipakai();
        $this->nama = $kategori->nama;
        $this->tipe = $kategori->tipe->value;
        $this->pembukuanId = $kategori->pembukuan_id ? (string) $kategori->pembukuan_id : 'global';
        $this->showForm = true;
    }

    public function batal(): void
    {
        $this->resetForm();
        $this->showForm = false;
    }

    public function simpan(): void
    {
        $pembukuanIdRaw = $this->pembukuanId;

        $this->validate([
            'nama' => [
                'required', 'string', 'max:255',
                Rule::unique('kategori', 'nama')
                    ->where(function ($query) use ($pembukuanIdRaw) {
                        $query->where('tipe', $this->tipe);

                        if ($pembukuanIdRaw === 'global') {
                            $query->whereNull('pembukuan_id');
                        } else {
                            $query->where('pembukuan_id', $pembukuanIdRaw);
                        }
                    })
                    ->ignore($this->editingId),
            ],
            'tipe' => ['required', Rule::enum(TipeTransaksi::class)],
            'pembukuanId' => [
                'required',
                function ($attribute, $value, $fail) {
                    if ($value !== 'global' && ! Pembukuan::whereKey($value)->exists()) {
                        $fail('Pembukuan tidak valid.');
                    }
                },
            ],
        ], [], [
            'nama' => 'nama',
            'tipe' => 'tipe',
            'pembukuanId' => 'pembukuan',
        ]);

        $pembukuanId = $pembukuanIdRaw === 'global' ? null : (int) $pembukuanIdRaw;

        if ($this->editingId) {
            $kategori = Kategori::findOrFail($this->editingId);

            $kategori->update([
                'nama' => $this->nama,
                // tipe terkunci kalau sudah dipakai di transaksi (docs/DATABASE_DESIGN.md)
                'tipe' => $kategori->sudahDipakai() ? $kategori->tipe : $this->tipe,
                'pembukuan_id' => $pembukuanId,
            ]);
        } else {
            Kategori::create([
                'nama' => $this->nama,
                'tipe' => $this->tipe,
                'pembukuan_id' => $pembukuanId,
            ]);
        }

        $this->resetForm();
        $this->showForm = false;
    }

    public function confirmHapus(int $id): void
    {
        $this->confirmingDeleteId = $id;
        $this->deleteErrorMessage = null;
    }

    public function batalHapus(): void
    {
        $this->confirmingDeleteId = null;
    }

    public function hapus(int $id): void
    {
        $kategori = Kategori::findOrFail($id);

        if ($kategori->sudahDipakai()) {
            $this->deleteErrorMessage = "Kategori \"{$kategori->nama}\" tidak bisa dihapus karena masih dipakai di transaksi.";
            $this->confirmingDeleteId = null;

            return;
        }

        $kategori->delete();
        $this->confirmingDeleteId = null;
        $this->deleteErrorMessage = null;
    }

    private function resetForm(): void
    {
        $this->reset(['editingId', 'editingLocked', 'nama', 'tipe', 'pembukuanId']);
        // tipe di-set eksplisit (bukan cuma andalkan default null lewat reset()) supaya
        // pilihan pertama di dropdown ("Pemasukan") beneran kesimpan kalau user gak
        // sentuh dropdown-nya sama sekali - dropdown visualnya udah keliatan terisi
        // sejak awal, jadi property-nya juga harus beneran senilai itu dari awal.
        $this->tipe = TipeTransaksi::Pemasukan->value;
        $this->pembukuanId = 'global';
        $this->resetValidation();
    }
}
