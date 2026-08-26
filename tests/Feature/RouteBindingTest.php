<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RouteBindingTest extends TestCase
{
    use RefreshDatabase;

    public function test_tipe_pembukuan_tidak_valid_di_url_menghasilkan_404(): void
    {
        $this->actingAs(User::factory()->create());
        $this->get('/transaksi/tidak-ada')->assertNotFound();
        $this->get('/hutang-piutang/tidak-ada')->assertNotFound();
    }
}
