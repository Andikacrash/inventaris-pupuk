<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Satukan kategori ke 4 pilar (Pupuk, Alat-alat pertanian, Herbisida, Pestisida),
     * remap produk, hapus kategori lama. Supplier pada produk dibuat opsional (nullable).
     */
    public function up(): void
    {
        $canonical = [
            'pupuk' => [
                'sort_order' => 10,
                'name' => 'Pupuk',
                'description' => 'Semua jenis pupuk (organik, kimia, NPK, urea, dan sejenisnya).',
            ],
            'alat' => [
                'sort_order' => 20,
                'name' => 'Alat-alat pertanian',
                'description' => 'Cangkul, sprayer, parang, ember, dan perlengkapan usaha tani lainnya.',
            ],
            'herbisida' => [
                'sort_order' => 30,
                'name' => 'Herbisida',
                'description' => 'Obat pembasmi gulma.',
            ],
            'pestisida' => [
                'sort_order' => 40,
                'name' => 'Pestisida',
                'description' => 'Obat pengendali hama dan penyakit tanaman.',
            ],
        ];

        $idsByKey = [];
        foreach ($canonical as $key => $row) {
            $existingId = DB::table('categories')->where('name', $row['name'])->value('id');
            if ($existingId) {
                DB::table('categories')->where('id', $existingId)->update([
                    'description' => $row['description'],
                    'sort_order' => $row['sort_order'],
                    'updated_at' => now(),
                ]);
                $idsByKey[$key] = (int) $existingId;
            } else {
                $idsByKey[$key] = (int) DB::table('categories')->insertGetId([
                    'name' => $row['name'],
                    'description' => $row['description'],
                    'sort_order' => $row['sort_order'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $mapNameToKey = function (string $name): string {
            $n = mb_strtolower(trim($name));
            if (str_contains($n, 'herbisida')) {
                return 'herbisida';
            }
            if (str_contains($n, 'pestisida')) {
                return 'pestisida';
            }
            if (str_contains($n, 'alat')) {
                return 'alat';
            }
            if (str_contains($n, 'pupuk')) {
                return 'pupuk';
            }

            return 'pupuk';
        };

        $categories = DB::table('categories')->get();
        foreach ($categories as $cat) {
            $key = $mapNameToKey((string) $cat->name);
            $targetId = $idsByKey[$key];
            if ((int) $cat->id !== $targetId) {
                DB::table('products')->where('category_id', $cat->id)->update(['category_id' => $targetId]);
            }
        }

        $keepIds = array_values($idsByKey);
        DB::table('categories')->whereNotIn('id', $keepIds)->delete();

        Schema::table('suppliers', function (Blueprint $table) {
            $table->string('contact_person')->nullable()->change();
            $table->string('phone')->nullable()->change();
            $table->text('address')->nullable()->change();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['supplier_id']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->unsignedBigInteger('supplier_id')->nullable()->change();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->foreign('supplier_id')->references('id')->on('suppliers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        // Tidak dibalik: penggabungan kategori dan data produk sudah mengubah relasi secara permanen.
    }
};
