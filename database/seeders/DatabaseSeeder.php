<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            SetorSeeder::class,
            AdminUserSeeder::class,
            CsvImportSeeder::class,
            ItensTreSeeder::class,
        ]);
    }
}
