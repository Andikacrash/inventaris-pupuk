<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    /**
     * Beberapa distributor saja — satu supplier bisa memasok banyak produk.
     */
    public function run(): void
    {
        $rows = [
            [
                'name' => 'CV Agro Maju',
                'contact_person' => 'Siti Aminah',
                'phone' => '022-7654321',
                'email' => 'siti@agromaju.com',
                'address' => 'Jl. Sudirman No. 456, Bandung',
            ],
            [
                'name' => 'UD Tani Sejahtera',
                'contact_person' => 'Ahmad Hidayat',
                'phone' => '031-9876543',
                'email' => 'ahmad@tanisejahtera.com',
                'address' => 'Jl. Ahmad Yani No. 789, Surabaya',
            ],
            [
                'name' => 'PT Mitra Sarana Tani',
                'contact_person' => 'Rudi Hartono',
                'phone' => '061-5543210',
                'email' => 'rudi@mitrasaranatani.co.id',
                'address' => 'Jl. Medan-Belawan Km 12, Medan',
            ],
        ];

        foreach ($rows as $row) {
            Supplier::updateOrCreate(
                ['name' => $row['name']],
                $row
            );
        }

        $this->command?->info('SupplierSeeder: '.count($rows).' distributor utama siap dipakai.');
    }
}
