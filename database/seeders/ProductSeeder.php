<?php



namespace Database\Seeders;



use App\Models\Category;

use App\Models\Product;

use App\Models\Supplier;

use Illuminate\Database\Seeder;



class ProductSeeder extends Seeder

{

    public function run(): void

    {

        $pupuk = Category::where('name', 'Pupuk')->first()

            ?? Category::where('name', 'like', '%Pupuk%')->first();



        if (! $pupuk) {

            $this->command?->warn('ProductSeeder: kategori Pupuk belum ada. Jalankan CategorySeeder dulu.');



            return;

        }



        // Satu distributor pupuk memasok semua merek/jenis pupuk di toko

        $distributorPupuk = Supplier::where('name', 'CV Agro Maju')->first()

            ?? Supplier::first();



        $rows = [

            ['barcode' => 'BRG-001', 'name' => 'Urea Subsidi', 'brand' => 'Pupuk Indonesia', 'jenis' => 'Subsidi', 'fungsi' => 'Mempercepat pertumbuhan daun dan batang sawit', 'price' => 90000, 'type' => 'kimia', 'stock' => 80, 'min' => 15],

            ['barcode' => 'BRG-002', 'name' => 'NPK Phonska Subsidi', 'brand' => 'Petrokimia Gresik', 'jenis' => 'Subsidi', 'fungsi' => 'Menambah kesuburan dan produksi buah sawit', 'price' => 92000, 'type' => 'kimia', 'stock' => 75, 'min' => 15],

            ['barcode' => 'BRG-003', 'name' => 'ZA Subsidi', 'brand' => 'Petrokimia Gresik', 'jenis' => 'Subsidi', 'fungsi' => 'Menambah unsur nitrogen dan sulfur', 'price' => 68000, 'type' => 'kimia', 'stock' => 70, 'min' => 15],

            ['barcode' => 'BRG-004', 'name' => 'Petroganik', 'brand' => 'Petroganik', 'jenis' => 'Subsidi', 'fungsi' => 'Memperbaiki struktur tanah dan akar sawit', 'price' => 35000, 'type' => 'organik', 'stock' => 60, 'min' => 10],

            ['barcode' => 'BRG-005', 'name' => 'Urea Non Subsidi', 'brand' => 'Pusri', 'jenis' => 'Non Subsidi', 'fungsi' => 'Membantu penghijauan daun sawit', 'price' => 325000, 'type' => 'kimia', 'stock' => 45, 'min' => 8],

            ['barcode' => 'BRG-006', 'name' => 'NPK Sawit 15-15-15', 'brand' => 'Mahkota Fertilizer', 'jenis' => 'Non Subsidi', 'fungsi' => 'Nutrisi lengkap untuk pertumbuhan sawit', 'price' => 425000, 'type' => 'kimia', 'stock' => 40, 'min' => 8],

            ['barcode' => 'BRG-007', 'name' => 'NPK Buah Sawit 11-15-16', 'brand' => 'Mahkota Fertilizer', 'jenis' => 'Non Subsidi', 'fungsi' => 'Memperbesar buah dan tandan sawit', 'price' => 250000, 'type' => 'kimia', 'stock' => 38, 'min' => 8],

            ['barcode' => 'BRG-008', 'name' => 'NPK Phonska Plus', 'brand' => 'Petrokimia Gresik', 'jenis' => 'Non Subsidi', 'fungsi' => 'Menambah unsur hara lengkap', 'price' => 340000, 'type' => 'kimia', 'stock' => 42, 'min' => 8],

            ['barcode' => 'BRG-009', 'name' => 'KCL/MOP Sawit', 'brand' => 'Mahkota Fertilizer', 'jenis' => 'Non Subsidi', 'fungsi' => 'Membantu pembentukan buah dan minyak sawit', 'price' => 586000, 'type' => 'kimia', 'stock' => 30, 'min' => 5],

            ['barcode' => 'BRG-010', 'name' => 'KCL Meroke MOP', 'brand' => 'Meroke', 'jenis' => 'Non Subsidi', 'fungsi' => 'Menambah unsur kalium agar buah lebih berat', 'price' => 798000, 'type' => 'kimia', 'stock' => 25, 'min' => 5],

            ['barcode' => 'BRG-011', 'name' => 'Dolomit Kapur Pertanian', 'brand' => 'Dolomite Indonesia', 'jenis' => 'Non Subsidi', 'fungsi' => 'Menetralkan pH tanah', 'price' => 50000, 'type' => 'kimia', 'stock' => 55, 'min' => 10],

            ['barcode' => 'BRG-012', 'name' => 'Dolomit Super', 'brand' => 'DGW Fertilizer', 'jenis' => 'Non Subsidi', 'fungsi' => 'Membantu penyerapan unsur hara', 'price' => 244000, 'type' => 'kimia', 'stock' => 35, 'min' => 8],

            ['barcode' => 'BRG-013', 'name' => 'Kieserite Sawit', 'brand' => 'Mahkota Fertilizer', 'jenis' => 'Non Subsidi', 'fungsi' => 'Menambah magnesium agar daun tidak kuning', 'price' => 448000, 'type' => 'kimia', 'stock' => 28, 'min' => 5],

            ['barcode' => 'BRG-014', 'name' => 'Kieserite Premium', 'brand' => 'Meroke', 'jenis' => 'Non Subsidi', 'fungsi' => 'Membantu fotosintesis dan kesehatan daun', 'price' => 520000, 'type' => 'kimia', 'stock' => 22, 'min' => 5],

            ['barcode' => 'BRG-015', 'name' => 'Borate Sawit', 'brand' => 'Fertila', 'jenis' => 'Non Subsidi', 'fungsi' => 'Mencegah busuk pucuk sawit', 'price' => 460000, 'type' => 'kimia', 'stock' => 20, 'min' => 5],

            ['barcode' => 'BRG-016', 'name' => 'SP-36 Sawit', 'brand' => 'Petrokimia Gresik', 'jenis' => 'Non Subsidi', 'fungsi' => 'Memperkuat akar dan mempercepat pertumbuhan', 'price' => 350000, 'type' => 'kimia', 'stock' => 32, 'min' => 8],

            ['barcode' => 'BRG-017', 'name' => 'NPK Grower Sawit', 'brand' => 'YaraMila', 'jenis' => 'Non Subsidi', 'fungsi' => 'Cocok untuk sawit usia muda', 'price' => 650000, 'type' => 'kimia', 'stock' => 18, 'min' => 5],

            ['barcode' => 'BRG-018', 'name' => 'NPK Produksi Sawit', 'brand' => 'YaraMila', 'jenis' => 'Non Subsidi', 'fungsi' => 'Meningkatkan hasil panen sawit', 'price' => 720000, 'type' => 'kimia', 'stock' => 15, 'min' => 5],

            ['barcode' => 'BRG-019', 'name' => 'Kompos Sawit Granule', 'brand' => 'Biotani', 'jenis' => 'Non Subsidi', 'fungsi' => 'Menambah bahan organik tanah', 'price' => 120000, 'type' => 'organik', 'stock' => 50, 'min' => 10],

            ['barcode' => 'BRG-020', 'name' => 'Humic Acid Granule', 'brand' => 'DGW Fertilizer', 'jenis' => 'Non Subsidi', 'fungsi' => 'Membantu akar menyerap pupuk lebih maksimal', 'price' => 280000, 'type' => 'organik', 'stock' => 30, 'min' => 8],

        ];



        foreach ($rows as $row) {

            $description = sprintf(

                'Jenis: %s. Berat: 50 Kg. %s',

                $row['jenis'],

                $row['fungsi']

            );



            Product::updateOrCreate(

                ['barcode' => $row['barcode']],

                [

                    'name' => $row['name'],

                    'brand' => $row['brand'],

                    'type' => $row['type'],

                    'unit' => 'karung',

                    'price' => $row['price'],

                    'stock_quantity' => $row['stock'],

                    'minimum_stock' => $row['min'],

                    'description' => $description,

                    'category_id' => $pupuk->id,

                    'supplier_id' => $distributorPupuk?->id,

                ]

            );

        }



        $this->command?->info('ProductSeeder: '.count($rows).' pupuk → distributor '.($distributorPupuk?->name ?? '—'));

    }

}


