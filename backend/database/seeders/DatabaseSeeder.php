<?php

namespace Database\Seeders;

use App\Models\User;
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
        $this->call([
            DataSeeder::class,
            MasterDataSeeder::class,
            CrmReunionSeeder::class,
            TemporadaSeeder::class,
            RolePermissionSeeder::class,
            UserSeeder::class,
            ClienteSeeder::class,
            EventoSeeder::class,
            ServicioSeeder::class,
            PlantillaDocumentoSeeder::class,
        ]);
    }
}
