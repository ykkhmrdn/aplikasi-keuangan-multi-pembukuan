<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seed 1 user login (aplikasi single-user, lihat docs/SCOPE_LOCK.md).
 * Username & password diambil dari .env supaya tidak hardcode kredensial
 * di kode yang ikut ter-commit ke git.
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['username' => env('ADMIN_USERNAME', 'admin')],
            [
                'name' => 'Reza',
                'password' => env('ADMIN_PASSWORD', 'password'),
            ]
        );
    }
}
