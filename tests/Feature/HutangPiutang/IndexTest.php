<?php

namespace Tests\Feature\HutangPiutang;

use App\Enums\StatusHutangPiutang;
use App\Livewire\HutangPiutang\Index;
use App\Models\HutangPiutang;
use App\Models\Pembukuan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase;

    private function buatDuaPembukuan(): array
    {
        return [
            Pembukuan::create(['nama' => 'Pribadi', 'tipe' => 'pribadi']),
            Pembukuan::create(['nama' => 'Kantor', 'tipe' => 'kantor']),
        ];
    }

    public function test_guest_tidak_bisa_akses_halaman_hutang_piutang(): void
    {
        [$pribadi] = $this->buatDuaPembukuan();

        $this->get("/hutang-piutang/{$pribadi->tipe->value}")->assertRedirect('/login');
    }

    public function test_user_bisa_lihat_piutang_dan_hutang(): void
    {
        $this->actingAs(User::factory()->create());
        [$pribadi, $kantor] = $this->buatDuaPembukuan();

        HutangPiutang::create([
            'dari_pembukuan_id' => $pribadi->id,
            'ke_pembukuan_id' => $kantor->id,
            'jumlah' => 200000,
            'tanggal' => now(),
        ]);

        Livewire::test(Index::class, ['pembukuan' => $pribadi])
            ->assertSee('Ke Kantor')
            ->assertSee('200.000');
    }

    public function test_user_bisa_catat_bon_baru(): void
    {
        $this->actingAs(User::factory()->create());
        [$pribadi, $kantor] = $this->buatDuaPembukuan();

        Livewire::test(Index::class, ['pembukuan' => $pribadi])
            ->call('tambah')
            ->set('dariPembukuanId', (string) $pribadi->id)
            ->set('kePembukuanId', (string) $kantor->id)
            ->set('jumlah', '300000')
            ->set('tanggal', now()->format('Y-m-d'))
            ->call('simpan')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('hutang_piutang', [
            'dari_pembukuan_id' => $pribadi->id,
            'ke_pembukuan_id' => $kantor->id,
            'jumlah' => 300000,
            'status' => 'belum_lunas',
        ]);
    }

    public function test_pembukuan_penerima_harus_beda_dengan_pemberi(): void
    {
        $this->actingAs(User::factory()->create());
        [$pribadi] = $this->buatDuaPembukuan();

        Livewire::test(Index::class, ['pembukuan' => $pribadi])
            ->call('tambah')
            ->set('dariPembukuanId', (string) $pribadi->id)
            ->set('kePembukuanId', (string) $pribadi->id)
            ->set('jumlah', '100000')
            ->set('tanggal', now()->format('Y-m-d'))
            ->call('simpan')
            ->assertHasErrors(['kePembukuanId']);
    }

    public function test_jumlah_bon_harus_lebih_dari_nol(): void
    {
        $this->actingAs(User::factory()->create());
        [$pribadi, $kantor] = $this->buatDuaPembukuan();

        Livewire::test(Index::class, ['pembukuan' => $pribadi])
            ->call('tambah')
            ->set('dariPembukuanId', (string) $pribadi->id)
            ->set('kePembukuanId', (string) $kantor->id)
            ->set('jumlah', '0')
            ->set('tanggal', now()->format('Y-m-d'))
            ->call('simpan')
            ->assertHasErrors(['jumlah']);
    }

    public function test_pelunasan_penuh_mengubah_status_jadi_lunas(): void
    {
        $this->actingAs(User::factory()->create());
        [$pribadi, $kantor] = $this->buatDuaPembukuan();
        $hp = HutangPiutang::create([
            'dari_pembukuan_id' => $pribadi->id, 'ke_pembukuan_id' => $kantor->id,
            'jumlah' => 500000, 'tanggal' => now(),
        ]);

        Livewire::test(Index::class, ['pembukuan' => $pribadi])
            ->call('melunasi', $hp->id)
            ->assertSet('jumlahPelunasan', '500000.00')
            ->call('simpanPelunasan')
            ->assertHasNoErrors();

        $hp->refresh();
        $this->assertEquals(StatusHutangPiutang::Lunas, $hp->status);
        $this->assertNotNull($hp->tanggal_lunas);
        $this->assertEquals('0.00', $hp->sisaOutstanding());
    }

    public function test_pelunasan_partial_tidak_mengubah_status(): void
    {
        $this->actingAs(User::factory()->create());
        [$pribadi, $kantor] = $this->buatDuaPembukuan();
        $hp = HutangPiutang::create([
            'dari_pembukuan_id' => $pribadi->id, 'ke_pembukuan_id' => $kantor->id,
            'jumlah' => 500000, 'tanggal' => now(),
        ]);

        Livewire::test(Index::class, ['pembukuan' => $pribadi])
            ->call('melunasi', $hp->id)
            ->set('jumlahPelunasan', '200000')
            ->call('simpanPelunasan')
            ->assertHasNoErrors();

        $hp->refresh();
        $this->assertEquals(StatusHutangPiutang::BelumLunas, $hp->status);
        $this->assertEquals('300000.00', $hp->sisaOutstanding());

        // pelunasan kedua melunasi sisanya
        Livewire::test(Index::class, ['pembukuan' => $pribadi])
            ->call('melunasi', $hp->id)
            ->assertSet('jumlahPelunasan', '300000.00')
            ->call('simpanPelunasan')
            ->assertHasNoErrors();

        $hp->refresh();
        $this->assertEquals(StatusHutangPiutang::Lunas, $hp->status);
    }

    public function test_pelunasan_melebihi_sisa_ditolak(): void
    {
        $this->actingAs(User::factory()->create());
        [$pribadi, $kantor] = $this->buatDuaPembukuan();
        $hp = HutangPiutang::create([
            'dari_pembukuan_id' => $pribadi->id, 'ke_pembukuan_id' => $kantor->id,
            'jumlah' => 500000, 'tanggal' => now(),
        ]);

        Livewire::test(Index::class, ['pembukuan' => $pribadi])
            ->call('melunasi', $hp->id)
            ->set('jumlahPelunasan', '600000')
            ->call('simpanPelunasan')
            ->assertHasErrors(['jumlahPelunasan']);

        $hp->refresh();
        $this->assertEquals(StatusHutangPiutang::BelumLunas, $hp->status);
        $this->assertEquals('500000.00', $hp->sisaOutstanding());
    }

    public function test_ringkasan_outstanding_benar(): void
    {
        $this->actingAs(User::factory()->create());
        [$pribadi, $kantor] = $this->buatDuaPembukuan();

        HutangPiutang::create([
            'dari_pembukuan_id' => $pribadi->id, 'ke_pembukuan_id' => $kantor->id,
            'jumlah' => 200000, 'tanggal' => now(),
        ]);
        HutangPiutang::create([
            'dari_pembukuan_id' => $kantor->id, 'ke_pembukuan_id' => $pribadi->id,
            'jumlah' => 50000, 'tanggal' => now(),
        ]);

        $this->assertEquals('200000.00', $pribadi->piutangOutstanding());
        $this->assertEquals('50000.00', $pribadi->hutangOutstanding());
    }

    public function test_saldo_konsisten_setelah_bon_dan_pelunasan(): void
    {
        $this->actingAs(User::factory()->create());
        [$pribadi, $kantor] = $this->buatDuaPembukuan();

        $hp = HutangPiutang::create([
            'dari_pembukuan_id' => $pribadi->id, 'ke_pembukuan_id' => $kantor->id,
            'jumlah' => 500000, 'tanggal' => now(),
        ]);

        // pribadi kasih bon: kas keluar 500rb, kantor terima bon: kas masuk 500rb
        $this->assertEquals('-500000.00', $pribadi->fresh()->saldo());
        $this->assertEquals('500000.00', $kantor->fresh()->saldo());

        Livewire::test(Index::class, ['pembukuan' => $pribadi])
            ->call('melunasi', $hp->id)
            ->call('simpanPelunasan');

        // kantor bayar utang penuh: kas keluar 500rb (balik ke 0)
        // pribadi terima pelunasan: kas masuk 500rb (balik ke 0)
        $this->assertEquals('0.00', $pribadi->fresh()->saldo());
        $this->assertEquals('0.00', $kantor->fresh()->saldo());
    }
}
