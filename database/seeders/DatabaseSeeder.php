<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (filter_var(env('SEED_DUMMY_DATA', false), FILTER_VALIDATE_BOOL)) {
            $this->call([
                DummyDataSeeder::class,
            ]);

            return;
        }

        $this->call([
            MasterAdminSeeder::class,
            DefaultRolesSeeder::class,
        ]);
    }
}
