<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@tjac.jus.br'],
            [
                'name' => 'Administrador',
                'password' => bcrypt('admin123'),
                'status' => 'ativo',
                'email_verified_at' => now(),
            ]
        );

        $admin->assignRole('super_admin');
    }
}
