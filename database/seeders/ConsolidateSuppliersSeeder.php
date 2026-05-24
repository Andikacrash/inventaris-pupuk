<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Database\Seeder;

class ConsolidateSuppliersSeeder extends Seeder
{
    /**
     * Satukan pemasok: beberapa distributor, banyak produk per distributor.
     * Hapus supplier lama yang tidak terpakai (nama per merek pabrik).
     */
    public function run(): void
    {
        $distributorPupuk = Supplier::firstOrCreate(
            ['name' => 'CV Agro Maju'],
            [
                'contact_person' => 'Siti Aminah',
                'phone' => '022-7654321',
                'email' => 'siti@agromaju.com',
                'address' => 'Jl. Sudirman No. 456, Bandung',
            ]
        );

        $distributorAgrokimia = Supplier::firstOrCreate(
            ['name' => 'UD Tani Sejahtera'],
            [
                'contact_person' => 'Ahmad Hidayat',
                'phone' => '031-9876543',
                'email' => 'ahmad@tanisejahtera.com',
                'address' => 'Jl. Ahmad Yani No. 789, Surabaya',
            ]
        );

        $distributorAlat = Supplier::firstOrCreate(
            ['name' => 'PT Mitra Sarana Tani'],
            [
                'contact_person' => 'Rudi Hartono',
                'phone' => '061-5543210',
                'email' => 'rudi@mitrasaranatani.co.id',
                'address' => 'Jl. Medan-Belawan Km 12, Medan',
            ]
        );

        $pupuk = Category::where('name', 'Pupuk')->first();
        if ($pupuk) {
            Product::where('category_id', $pupuk->id)->update(['supplier_id' => $distributorPupuk->id]);
        }

        foreach (['Herbisida', 'Pestisida'] as $categoryName) {
            $cat = Category::where('name', $categoryName)->first();
            if ($cat) {
                Product::where('category_id', $cat->id)->update(['supplier_id' => $distributorAgrokimia->id]);
            }
        }

        $alat = Category::where('name', 'like', '%Alat%')->first();
        if ($alat) {
            Product::where('category_id', $alat->id)->update(['supplier_id' => $distributorAlat->id]);
        }

        $usedIds = Product::whereNotNull('supplier_id')->pluck('supplier_id')->unique()->filter();
        $deleted = Supplier::whereNotIn('id', $usedIds)->delete();

        $this->command?->info("ConsolidateSuppliersSeeder: produk dipetakan ke {$usedIds->count()} distributor; {$deleted} pemasok lama dihapus.");
    }
}
