<?php

use App\Livewire\Auth\Login;
use App\Livewire\Dashboard;
use App\Livewire\HutangPiutang\Index as HutangPiutangIndex;
use App\Livewire\Kategori\Index as KategoriIndex;
use App\Livewire\Transaksi\Index as TransaksiIndex;
use App\Livewire\Transfer\Index as TransferIndex;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/login', Login::class)->middleware('guest')->name('login');

Route::post('/logout', function () {
    Auth::logout();

    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect()->route('login');
})->middleware('auth')->name('logout');

Route::get('/', Dashboard::class)->middleware('auth')->name('dashboard');

Route::get('/kategori', KategoriIndex::class)->middleware('auth')->name('kategori.index');

Route::get('/transaksi/{pembukuan}', TransaksiIndex::class)->middleware('auth')->name('transaksi.index');

Route::get('/transfer', TransferIndex::class)->middleware('auth')->name('transfer.index');

Route::get('/hutang-piutang/{pembukuan}', HutangPiutangIndex::class)->middleware('auth')->name('hutang-piutang.index');
