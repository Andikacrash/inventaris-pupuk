<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Urutan sort_order: pupuk (10) → herbisida (30) → pestisida (40).
     * updateOrCreate mempertahankan nama unik dan memperbarui urutan jika seeder dijalankan ulang.
     */
    public function run(): void
    {
        $rows = [
            ['sort_order' => 10, 'name' => 'Pupuk', 'description' => 'Semua jenis pupuk (organik, kimia, NPK, urea, dan sejenisnya).'],
            ['sort_order' => 30, 'name' => 'Herbisida', 'description' => 'Obat pembasmi gulma.'],
            ['sort_order' => 40, 'name' => 'Pestisida', 'description' => 'Obat pengendali hama dan penyakit tanaman.'],
        ];

        foreach ($rows as $row) {
            Category::updateOrCreate(
                ['name' => $row['name']],
                [
                    'description' => $row['description'],
                    'sort_order' => $row['sort_order'],
                ]
            );
        }
    }
}
