<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            CategorieSeeder::class,
            FournisseurSeeder::class,
            ClientSeeder::class,
            ProduitSeeder::class,
            StockSeeder::class,
            ParametreSeeder::class,
            CompteFinancierSeeder::class,
        ]);
    }
}
