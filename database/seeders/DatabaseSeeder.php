<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            CategorySeeder::class,
            SupplierSeeder::class,
            ProductSeeder::class,
            CropProtectionProductSeeder::class,
            ConsolidateSuppliersSeeder::class,
            SaleSeeder::class,
            DebtSeeder::class,
            InstallmentSeeder::class,
            StockMovementSeeder::class,
            NormalizeUsersSeeder::class,
        ]);
    }
}
