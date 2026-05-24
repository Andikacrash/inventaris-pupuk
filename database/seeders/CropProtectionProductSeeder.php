<?php



namespace Database\Seeders;



use App\Models\Category;

use App\Models\Product;

use App\Models\Supplier;

use Illuminate\Database\Seeder;



class CropProtectionProductSeeder extends Seeder

{

    public function run(): void

    {

        $herbisida = Category::where('name', 'Herbisida')->first();

        $pestisida = Category::where('name', 'Pestisida')->first();



        if (! $herbisida || ! $pestisida) {

            $this->command?->warn('CropProtectionProductSeeder: kategori Herbisida/Pestisida belum ada.');



            return;

        }



        // Satu distributor agrokimia memasok herbisida & pestisida (berbagai merek)

        $distributorAgrokimia = Supplier::where('name', 'UD Tani Sejahtera')->first()

            ?? Supplier::first();



        $rows = [

            ['barcode' => 'BRG-021', 'name' => 'Roundup 486 SL', 'brand' => 'Monsanto', 'kategori' => 'Herbisida', 'jenis' => 'Herbisida', 'fungsi' => 'Membasmi gulma dan rumput liar', 'ukuran' => '1 Liter', 'unit' => 'liter', 'price_range' => '85000-120000', 'stock' => 40, 'min' => 8],

            ['barcode' => 'BRG-022', 'name' => 'Gramoxone', 'brand' => 'Syngenta', 'kategori' => 'Herbisida', 'jenis' => 'Herbisida', 'fungsi' => 'Membunuh gulma dengan cepat', 'ukuran' => '1 Liter', 'unit' => 'liter', 'price_range' => '70000-110000', 'stock' => 38, 'min' => 8],

            ['barcode' => 'BRG-023', 'name' => 'Noxone', 'brand' => 'Nufarm', 'kategori' => 'Herbisida', 'jenis' => 'Herbisida', 'fungsi' => 'Mengendalikan gulma di kebun sawit', 'ukuran' => '1 Liter', 'unit' => 'liter', 'price_range' => '65000-95000', 'stock' => 35, 'min' => 8],

            ['barcode' => 'BRG-024', 'name' => 'Bimastar', 'brand' => 'Bima Tani', 'kategori' => 'Herbisida', 'jenis' => 'Herbisida', 'fungsi' => 'Membasmi alang-alang dan rumput', 'ukuran' => '1 Liter', 'unit' => 'liter', 'price_range' => '55000-90000', 'stock' => 42, 'min' => 8],

            ['barcode' => 'BRG-025', 'name' => 'DMA 6', 'brand' => 'Dow AgroScience', 'kategori' => 'Herbisida', 'jenis' => 'Herbisida', 'fungsi' => 'Mengendalikan gulma daun lebar', 'ukuran' => '1 Liter', 'unit' => 'liter', 'price_range' => '75000-130000', 'stock' => 30, 'min' => 6],

            ['barcode' => 'BRG-026', 'name' => 'Ally Plus', 'brand' => 'DuPont', 'kategori' => 'Herbisida', 'jenis' => 'Herbisida', 'fungsi' => 'Pengendali gulma keras sawit', 'ukuran' => '100 Gram', 'unit' => 'kg', 'price_range' => '180000-250000', 'stock' => 25, 'min' => 5],

            ['barcode' => 'BRG-027', 'name' => 'Sidamin 865 SL', 'brand' => 'Sidam', 'kategori' => 'Herbisida', 'jenis' => 'Herbisida', 'fungsi' => 'Membasmi gulma semak', 'ukuran' => '1 Liter', 'unit' => 'liter', 'price_range' => '70000-100000', 'stock' => 36, 'min' => 8],

            ['barcode' => 'BRG-028', 'name' => 'Supremo 480 SL', 'brand' => 'Supremo', 'kategori' => 'Herbisida', 'jenis' => 'Herbisida', 'fungsi' => 'Pengendali rumput liar sawit', 'ukuran' => '1 Liter', 'unit' => 'liter', 'price_range' => '60000-95000', 'stock' => 40, 'min' => 8],

            ['barcode' => 'BRG-029', 'name' => 'Regent 50 SC', 'brand' => 'BASF', 'kategori' => 'Pestisida', 'jenis' => 'Insektisida', 'fungsi' => 'Membasmi ulat dan hama sawit', 'ukuran' => '1 Liter', 'unit' => 'liter', 'price_range' => '250000-350000', 'stock' => 22, 'min' => 5],

            ['barcode' => 'BRG-030', 'name' => 'Decis 25 EC', 'brand' => 'Bayer', 'kategori' => 'Pestisida', 'jenis' => 'Insektisida', 'fungsi' => 'Mengendalikan serangga daun', 'ukuran' => '250 Ml', 'unit' => 'liter', 'price_range' => '45000-90000', 'stock' => 45, 'min' => 10],

            ['barcode' => 'BRG-031', 'name' => 'Curacron 500 EC', 'brand' => 'Syngenta', 'kategori' => 'Pestisida', 'jenis' => 'Insektisida', 'fungsi' => 'Membasmi ulat api sawit', 'ukuran' => '1 Liter', 'unit' => 'liter', 'price_range' => '180000-260000', 'stock' => 28, 'min' => 6],

            ['barcode' => 'BRG-032', 'name' => 'Matador 25 EC', 'brand' => 'Syngenta', 'kategori' => 'Pestisida', 'jenis' => 'Insektisida', 'fungsi' => 'Pengendali hama penggerek', 'ukuran' => '250 Ml', 'unit' => 'liter', 'price_range' => '55000-100000', 'stock' => 40, 'min' => 8],

            ['barcode' => 'BRG-033', 'name' => 'Furadan 3GR', 'brand' => 'FMC', 'kategori' => 'Pestisida', 'jenis' => 'Insektisida', 'fungsi' => 'Mengatasi hama tanah dan kumbang', 'ukuran' => '1 Kg', 'unit' => 'kg', 'price_range' => '35000-70000', 'stock' => 50, 'min' => 10],

            ['barcode' => 'BRG-034', 'name' => 'Virtako', 'brand' => 'Syngenta', 'kategori' => 'Pestisida', 'jenis' => 'Insektisida', 'fungsi' => 'Membasmi ulat dan hama daun', 'ukuran' => '100 Ml', 'unit' => 'liter', 'price_range' => '85000-150000', 'stock' => 35, 'min' => 8],

            ['barcode' => 'BRG-035', 'name' => 'Score 250 EC', 'brand' => 'Syngenta', 'kategori' => 'Pestisida', 'jenis' => 'Fungisida', 'fungsi' => 'Mengatasi jamur pada tanaman', 'ukuran' => '250 Ml', 'unit' => 'liter', 'price_range' => '70000-120000', 'stock' => 38, 'min' => 8],

            ['barcode' => 'BRG-036', 'name' => 'Dithane M-45', 'brand' => 'Dow AgroScience', 'kategori' => 'Pestisida', 'jenis' => 'Fungisida', 'fungsi' => 'Mencegah penyakit bercak daun', 'ukuran' => '1 Kg', 'unit' => 'kg', 'price_range' => '65000-110000', 'stock' => 45, 'min' => 10],

            ['barcode' => 'BRG-037', 'name' => 'Antracol 70 WP', 'brand' => 'Bayer', 'kategori' => 'Pestisida', 'jenis' => 'Fungisida', 'fungsi' => 'Mencegah busuk batang dan daun', 'ukuran' => '1 Kg', 'unit' => 'kg', 'price_range' => '80000-140000', 'stock' => 40, 'min' => 8],

            ['barcode' => 'BRG-038', 'name' => 'Ridomil Gold', 'brand' => 'Syngenta', 'kategori' => 'Pestisida', 'jenis' => 'Fungisida', 'fungsi' => 'Mengatasi busuk akar dan jamur', 'ukuran' => '250 Gram', 'unit' => 'kg', 'price_range' => '90000-170000', 'stock' => 32, 'min' => 6],

            ['barcode' => 'BRG-039', 'name' => 'Amistar Top', 'brand' => 'Syngenta', 'kategori' => 'Pestisida', 'jenis' => 'Fungisida', 'fungsi' => 'Perlindungan daun dan batang', 'ukuran' => '250 Ml', 'unit' => 'liter', 'price_range' => '120000-220000', 'stock' => 28, 'min' => 6],

            ['barcode' => 'BRG-040', 'name' => 'Bestox', 'brand' => 'Best Agro', 'kategori' => 'Pestisida', 'jenis' => 'Insektisida', 'fungsi' => 'Membasmi serangga pengganggu sawit', 'ukuran' => '500 Ml', 'unit' => 'liter', 'price_range' => '60000-95000', 'stock' => 42, 'min' => 8],

        ];



        foreach ($rows as $row) {

            $category = $row['kategori'] === 'Herbisida' ? $herbisida : $pestisida;

            $price = $this->priceFromRange($row['price_range']);



            $description = sprintf(

                'Jenis: %s. Ukuran: %s. Kisaran harga: Rp%s. %s',

                $row['jenis'],

                $row['ukuran'],

                number_format($price, 0, ',', '.'),

                $row['fungsi']

            );



            Product::updateOrCreate(

                ['barcode' => $row['barcode']],

                [

                    'name' => $row['name'],

                    'brand' => $row['brand'],

                    'type' => 'kimia',

                    'unit' => $row['unit'],

                    'price' => $price,

                    'stock_quantity' => $row['stock'],

                    'minimum_stock' => $row['min'],

                    'description' => $description,

                    'category_id' => $category->id,

                    'supplier_id' => $distributorAgrokimia?->id,

                ]

            );

        }



        $this->command?->info('CropProtectionProductSeeder: '.count($rows).' agrokimia → distributor '.($distributorAgrokimia?->name ?? '—'));

    }



    private function priceFromRange(string $range): int

    {

        $parts = preg_split('/[^0-9]+/', $range);

        $nums = array_values(array_filter(array_map('intval', $parts), fn ($n) => $n > 0));



        if (count($nums) >= 2) {

            return (int) round(($nums[0] + $nums[1]) / 2);

        }



        return $nums[0] ?? 0;

    }

}


