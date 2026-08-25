<?php

namespace App\Livewire;

use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Placeholder halaman utama setelah login, dibangun lengkap di Tahap Dashboard
 * (lihat docs/TODO.md). Untuk Tahap Auth ini cukup buktikan middleware jalan.
 */
#[Layout('layouts.app')]
class Dashboard extends Component
{
    public function render()
    {
        return view('livewire.dashboard');
    }
}
