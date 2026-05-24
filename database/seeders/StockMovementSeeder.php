<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StockMovementSeeder extends Seeder
{
    /** Pola lama yang perlu dibersihkan */
    private const LEGACY_NOTE_MARKERS = [
        'Data demo stok%',
        'Penjualan piutang%',
        'Penjualan kasir (demo)%',
        'Penjualan walk-in (demo)%',
    ];

    public function run(): void
    {
        $kasirUsers = User::where('role', 'kasir')->orderBy('id')->get();
        $adminUser = User::where('role', 'admin')->first()
            ?? User::first();

        if (! $adminUser) {
            $this->command?->warn('StockMovementSeeder: tidak ada user.');

            return;
        }

        $products = Product::orderBy('id')->get();
        if ($products->count() < 3) {
            $this->command?->warn('StockMovementSeeder: produk kurang.');

            return;
        }

        $this->removePreviousSeedData();

        $rows = $this->buildIncomingSchedule($products, $kasirUsers, $adminUser);

        DB::transaction(function () use ($rows, $adminUser, $products) {
            $productMap = $products->keyBy('id');
            $refSeq = 2000;

            foreach ($rows as $row) {
                $product = $productMap->get($row['product_id']);
                if (! $product) {
                    continue;
                }

                $qty = (int) $row['quantity'];
                $refSeq++;
                $userId = $row['user_id'] ?? $adminUser->id;

                $product->increment('stock_quantity', $qty);

                $movement = StockMovement::create([
                    'product_id' => $product->id,
                    'type' => 'in',
                    'quantity' => $qty,
                    'reference_type' => 'adjustment',
                    'reference_id' => $refSeq,
                    'notes' => $row['notes'],
                    'user_id' => $userId,
                ]);

                $at = Carbon::today()
                    ->subDays((int) $row['days_ago'])
                    ->setTime((int) $row['hour'], (int) $row['minute'], 0);

                $movement->created_at = $at;
                $movement->updated_at = $at;
                $movement->saveQuietly();
            }
        });

        $this->command?->info('StockMovementSeeder: '.count($rows).' penerimaan barang (stok masuk) dicatat.');
        $this->command?->info('StockMovementSeeder: penjualan keluar mengikuti transaksi kasir (DebtSeeder).');
    }

    /**
     * Jadwal stok masuk — diselang-seling tanggal dengan penjualan kasir (hari ganjil/ganjil beda jam).
     *
     * @return list<array<string, mixed>>
     */
    /**
     * Stok masuk: dicatat Admin (gudang) dan Kasir (terima barang di toko) — selang-seling.
     */
    private function buildIncomingSchedule($products, $kasirUsers, User $adminUser): array
    {
        $pick = fn (int $offset = 0) => $products[$offset % $products->count()]->id;
        $kasir = $kasirUsers->isNotEmpty() ? $kasirUsers : collect();

        $suppliers = ['PT Pupuk Nusantara', 'CV Tani Subur', 'Distributor Agro Mandiri', 'Supplier lokal kecamatan'];

        $adminId = $adminUser->id;
        $kasirId = fn (int $i) => $kasir->isNotEmpty() ? $kasir[$i % $kasir->count()]->id : $adminId;

        $schedule = [
            ['product_id' => $pick(0), 'quantity' => 120, 'days_ago' => 41, 'hour' => 7, 'minute' => 30, 'notes' => 'Penerimaan PO — '.$suppliers[0], 'user_id' => $adminId],
            ['product_id' => $pick(1), 'quantity' => 80, 'days_ago' => 39, 'hour' => 8, 'minute' => 0, 'notes' => 'Barang masuk gudang — '.$suppliers[1], 'user_id' => $kasirId(0)],
            ['product_id' => $pick(2), 'quantity' => 60, 'days_ago' => 37, 'hour' => 7, 'minute' => 45, 'notes' => 'Penerimaan supplier — pestisida', 'user_id' => $adminId],
            ['product_id' => $pick(3), 'quantity' => 100, 'days_ago' => 35, 'hour' => 8, 'minute' => 15, 'notes' => 'Restock gudang utama', 'user_id' => $kasirId(1)],
            ['product_id' => $pick(4), 'quantity' => 45, 'days_ago' => 33, 'hour' => 7, 'minute' => 20, 'notes' => 'Penerimaan mingguan — '.$suppliers[2], 'user_id' => $adminId],
            ['product_id' => $pick(5), 'quantity' => 200, 'days_ago' => 31, 'hour' => 8, 'minute' => 40, 'notes' => 'Stok masuk musim tanam', 'user_id' => $kasirId(0)],
            ['product_id' => $pick(6), 'quantity' => 35, 'days_ago' => 29, 'hour' => 7, 'minute' => 10, 'notes' => 'Penerimaan dari '.$suppliers[3], 'user_id' => $adminId],
            ['product_id' => $pick(7), 'quantity' => 90, 'days_ago' => 27, 'hour' => 8, 'minute' => 25, 'notes' => 'Restock rak display toko', 'user_id' => $kasirId(1)],
            ['product_id' => $pick(8), 'quantity' => 55, 'days_ago' => 25, 'hour' => 7, 'minute' => 50, 'notes' => 'Penerimaan barang baru', 'user_id' => $adminId],
            ['product_id' => $pick(9), 'quantity' => 70, 'days_ago' => 23, 'hour' => 8, 'minute' => 5, 'notes' => 'Koreksi opname — stok masuk', 'user_id' => $kasirId(0)],
            ['product_id' => $pick(10), 'quantity' => 40, 'days_ago' => 19, 'hour' => 7, 'minute' => 35, 'notes' => 'Penerimaan harian gudang', 'user_id' => $adminId],
            ['product_id' => $pick(11), 'quantity' => 65, 'days_ago' => 15, 'hour' => 8, 'minute' => 20, 'notes' => 'Restock persiapan promo', 'user_id' => $kasirId(1)],
            ['product_id' => $pick(0), 'quantity' => 50, 'days_ago' => 11, 'hour' => 7, 'minute' => 55, 'notes' => 'Penerimaan PO — herbisida', 'user_id' => $adminId],
            ['product_id' => $pick(2), 'quantity' => 75, 'days_ago' => 7, 'hour' => 8, 'minute' => 10, 'notes' => 'Barang masuk — '.$suppliers[0], 'user_id' => $kasirId(0)],
            ['product_id' => $pick(4), 'quantity' => 48, 'days_ago' => 4, 'hour' => 7, 'minute' => 40, 'notes' => 'Penerimaan supplier lokal', 'user_id' => $adminId],
            ['product_id' => $pick(6), 'quantity' => 30, 'days_ago' => 1, 'hour' => 8, 'minute' => 0, 'notes' => 'Restock awal shift kasir', 'user_id' => $kasirId(1)],
        ];

        return $schedule;
    }

    private function removePreviousSeedData(): void
    {
        $query = StockMovement::query()->where('type', 'in')->where('reference_type', 'adjustment');

        $query->where(function ($q) {
            foreach (self::LEGACY_NOTE_MARKERS as $pattern) {
                $q->orWhere('notes', 'like', $pattern);
            }
            $q->orWhere('notes', 'like', 'Data demo stok%');
        });

        $movements = $query->get();

        if ($movements->isEmpty()) {
            return;
        }

        DB::transaction(function () use ($movements) {
            foreach ($movements as $movement) {
                $product = Product::find($movement->product_id);
                if ($product) {
                    $product->decrement('stock_quantity', $movement->quantity);
                }
            }

            StockMovement::query()
                ->whereIn('id', $movements->pluck('id'))
                ->delete();
        });

        StockMovement::query()
            ->where('notes', 'like', 'Data demo stok%')
            ->orWhere('notes', 'like', 'Penjualan piutang%')
            ->orWhere('notes', 'like', '%(demo)%')
            ->get()
            ->each(function (StockMovement $m) {
                $p = Product::find($m->product_id);
                if ($p) {
                    if ($m->type === 'in') {
                        $p->decrement('stock_quantity', $m->quantity);
                    } else {
                        $p->increment('stock_quantity', $m->quantity);
                    }
                }
                $m->delete();
            });
    }
}
