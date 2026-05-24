<?php

namespace Database\Seeders;

use App\Models\Debt;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockMovement;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DebtSeeder extends Seeder
{
    /** Nama pelanggan seed — dipakai untuk bersihkan data lama saat seed ulang */
    private const SEED_CUSTOMERS = [
        'Budi Santoso', 'Siti Rahayu', 'Ahmad Wijaya', 'Dewi Lestari', 'Rudi Hartono',
        'Maya Putri', 'Joko Susilo', 'Ani Wulandari', 'Hendra Kusuma', 'Fitri Handayani',
        'Agus Prasetyo', 'Rina Melati', 'Yusuf Maulana', 'Kartini Sari', 'Eko Nugroho',
    ];

    private const WALK_IN_CUSTOMERS = [
        'Pelanggan Umum', 'Walk-in', 'Pak Tani', 'Bu Wati', 'Koperasi Makmur',
        'UD Subur Jaya', 'Toko Tani Sejahtera', 'Pelanggan Langganan',
    ];

    private const DEBT_NOTES = [
        'Tempo 30 hari — nota kredit toko',
        'Bayar sebagian di kasir, sisanya tempo',
        'Pelanggan langganan, janji lunas minggu depan',
        'Nota tempo — sudah ada DP tunai',
        'Piutang hasil penjualan di kasir',
        'Perjanjian bayar setelah panen',
        null,
    ];

    public function run(): void
    {
        $kasirUsers = User::where('role', 'kasir')->orderBy('id')->get();
        if ($kasirUsers->isEmpty()) {
            $kasirUsers = collect([User::first()])->filter();
        }

        if ($kasirUsers->isEmpty()) {
            $this->command?->warn('DebtSeeder: tidak ada user kasir.');

            return;
        }

        $products = Product::orderBy('id')->get();
        if ($products->isEmpty()) {
            $this->command?->warn('DebtSeeder: tidak ada produk.');

            return;
        }

        $this->removePreviousSeedData();

        $creditRows = [
            ['name' => 'Budi Santoso', 'phone' => '081234560001', 'total' => 250000, 'paid' => 0, 'status' => 'unpaid', 'days_ago' => 40],
            ['name' => 'Siti Rahayu', 'phone' => '081234560002', 'total' => 180000, 'paid' => 80000, 'status' => 'partial', 'days_ago' => 38],
            ['name' => 'Ahmad Wijaya', 'phone' => '081234560003', 'total' => 320000, 'paid' => 120000, 'status' => 'partial', 'days_ago' => 36],
            ['name' => 'Dewi Lestari', 'phone' => '081234560004', 'total' => 95000, 'paid' => 0, 'status' => 'unpaid', 'days_ago' => 34],
            ['name' => 'Rudi Hartono', 'phone' => '081234560005', 'total' => 410000, 'paid' => 410000, 'status' => 'paid', 'days_ago' => 32],
            ['name' => 'Maya Putri', 'phone' => '081234560006', 'total' => 155000, 'paid' => 50000, 'status' => 'partial', 'days_ago' => 30],
            ['name' => 'Joko Susilo', 'phone' => '081234560007', 'total' => 275000, 'paid' => 0, 'status' => 'unpaid', 'days_ago' => 28],
            ['name' => 'Ani Wulandari', 'phone' => '081234560008', 'total' => 198000, 'paid' => 198000, 'status' => 'paid', 'days_ago' => 26],
            ['name' => 'Hendra Kusuma', 'phone' => '081234560009', 'total' => 520000, 'paid' => 200000, 'status' => 'partial', 'days_ago' => 24],
            ['name' => 'Fitri Handayani', 'phone' => '081234560010', 'total' => 125000, 'paid' => 25000, 'status' => 'partial', 'days_ago' => 22],
            ['name' => 'Agus Prasetyo', 'phone' => '081234560011', 'total' => 340000, 'paid' => 0, 'status' => 'unpaid', 'days_ago' => 18],
            ['name' => 'Rina Melati', 'phone' => '081234560012', 'total' => 88000, 'paid' => 88000, 'status' => 'paid', 'days_ago' => 14],
            ['name' => 'Yusuf Maulana', 'phone' => '081234560013', 'total' => 460000, 'paid' => 150000, 'status' => 'partial', 'days_ago' => 10],
            ['name' => 'Kartini Sari', 'phone' => '081234560014', 'total' => 210000, 'paid' => 0, 'status' => 'unpaid', 'days_ago' => 6],
            ['name' => 'Eko Nugroho', 'phone' => '081234560015', 'total' => 175000, 'paid' => 100000, 'status' => 'partial', 'days_ago' => 3],
        ];

        $cashRows = [
            ['name' => 'Pelanggan Umum', 'phone' => null, 'total' => 85000, 'method' => 'cash', 'days_ago' => 39],
            ['name' => 'Walk-in', 'phone' => null, 'total' => 170000, 'method' => 'cash', 'days_ago' => 37],
            ['name' => 'Pak Tani', 'phone' => '081355511122', 'total' => 255000, 'method' => 'transfer', 'days_ago' => 35],
            ['name' => 'Bu Wati', 'phone' => '081366622233', 'total' => 127500, 'method' => 'cash', 'days_ago' => 33],
            ['name' => 'Koperasi Makmur', 'phone' => '081377733344', 'total' => 340000, 'method' => 'transfer', 'days_ago' => 31],
            ['name' => 'UD Subur Jaya', 'phone' => '081388844455', 'total' => 425000, 'method' => 'transfer', 'days_ago' => 29],
            ['name' => 'Toko Tani Sejahtera', 'phone' => '081399955566', 'total' => 93500, 'method' => 'cash', 'days_ago' => 27],
            ['name' => 'Pelanggan Langganan', 'phone' => '081311122233', 'total' => 510000, 'method' => 'cash', 'days_ago' => 25],
            ['name' => 'Pelanggan Umum', 'phone' => null, 'total' => 68000, 'method' => 'cash', 'days_ago' => 21],
            ['name' => 'Walk-in', 'phone' => null, 'total' => 153000, 'method' => 'cash', 'days_ago' => 17],
            ['name' => 'Pak Tani', 'phone' => '081355511122', 'total' => 289000, 'method' => 'transfer', 'days_ago' => 13],
            ['name' => 'Bu Wati', 'phone' => '081366622233', 'total' => 102000, 'method' => 'cash', 'days_ago' => 9],
            ['name' => 'Koperasi Makmur', 'phone' => '081377733344', 'total' => 374000, 'method' => 'transfer', 'days_ago' => 5],
            ['name' => 'Pelanggan Umum', 'phone' => null, 'total' => 76500, 'method' => 'cash', 'days_ago' => 2],
        ];

        $invoiceSeqByDate = [];
        $creditCount = 0;
        $cashCount = 0;
        $kasirIndex = 0;

        foreach ($creditRows as $i => $row) {
            if ($i % 2 === 0 && isset($cashRows[$cashCount])) {
                $kasir = $kasirUsers[$kasirIndex % $kasirUsers->count()];
                $kasirIndex++;
                $this->createCashSale($cashRows[$cashCount], $products, $kasir, $invoiceSeqByDate);
                $cashCount++;
            }

            $kasir = $kasirUsers[$kasirIndex % $kasirUsers->count()];
            $kasirIndex++;
            $this->createCreditSale($row, $products, $kasir, $invoiceSeqByDate, $i);
            $creditCount++;
        }

        while ($cashCount < count($cashRows)) {
            $kasir = $kasirUsers[$kasirIndex % $kasirUsers->count()];
            $kasirIndex++;
            $this->createCashSale($cashRows[$cashCount], $products, $kasir, $invoiceSeqByDate);
            $cashCount++;
        }

        $synced = $this->syncMissingSaleStockMovements($kasirUsers->first());

        $this->command?->info("DebtSeeder: {$creditCount} penjualan piutang + {$cashCount} penjualan tunai kasir.");
        if ($synced > 0) {
            $this->command?->info("DebtSeeder: {$synced} baris stok keluar diselaraskan.");
        }
    }

    private function nextInvoice(array &$seqByDate, Carbon $saleDate): string
    {
        $key = $saleDate->format('Ymd');
        $seqByDate[$key] = ($seqByDate[$key] ?? 0) + 1;

        return 'INV'.$key.str_pad((string) $seqByDate[$key], 4, '0', STR_PAD_LEFT);
    }

    private function pickProduct($products, int $index): Product
    {
        return $products[$index % $products->count()];
    }

    private function createCashSale(array $row, $products, User $kasir, array &$invoiceSeqByDate): void
    {
        $saleDate = Carbon::today()->subDays((int) $row['days_ago']);
        $product = $this->pickProduct($products, (int) $saleDate->format('d'));
        $unitPrice = max(1, (float) $product->price);
        $total = (float) $row['total'];
        $qty = max(1, (int) round($total / $unitPrice));
        $subtotal = $qty * $unitPrice;
        $hour = 8 + (($row['days_ago'] + $kasir->id) % 9);
        $minute = ($row['days_ago'] * 7) % 60;

        $sale = Sale::create([
            'invoice_number' => $this->nextInvoice($invoiceSeqByDate, $saleDate),
            'customer_name' => $row['name'],
            'customer_phone' => $row['phone'],
            'delivery_method' => 'pickup',
            'sale_date' => $saleDate->format('Y-m-d'),
            'discount' => 0,
            'total_amount' => $subtotal > 0 ? $subtotal : $total,
            'payment_method' => $row['method'],
            'payment_amount' => $subtotal > 0 ? $subtotal : $total,
            'change_amount' => 0,
            'debt_amount' => 0,
            'debt_status' => 'paid',
            'status' => 'completed',
            'user_id' => $kasir->id,
        ]);

        SaleItem::create([
            'sale_id' => $sale->id,
            'product_id' => $product->id,
            'quantity' => $qty,
            'unit_price' => $unitPrice,
            'subtotal' => $subtotal > 0 ? $subtotal : $total,
        ]);

        $this->recordSaleStockOut($sale, $product, $qty, $kasir->id, $saleDate->copy()->setTime($hour, $minute, 0));
    }

    private function createCreditSale(array $row, $products, User $kasir, array &$invoiceSeqByDate, int $index): void
    {
        $saleDate = Carbon::today()->subDays((int) $row['days_ago']);
        $product = $this->pickProduct($products, $index + 3);
        $unitPrice = max(1, (float) $product->price);
        $total = (float) $row['total'];
        $paid = min($total, (float) $row['paid']);
        $remaining = max(0, $total - $paid);
        $status = $row['status'];
        $dueDate = Carbon::today()->addDays($status === 'paid' ? 0 : rand(7, 45));
        $qty = max(1, (int) round($total / $unitPrice));
        $subtotal = $qty * $unitPrice;
        $hour = 9 + (($row['days_ago'] + $index) % 8);
        $minute = ($index * 11) % 60;
        $debtNote = self::DEBT_NOTES[$index % count(self::DEBT_NOTES)];

        $sale = Sale::create([
            'invoice_number' => $this->nextInvoice($invoiceSeqByDate, $saleDate),
            'customer_name' => $row['name'],
            'customer_phone' => $row['phone'],
            'delivery_method' => 'pickup',
            'sale_date' => $saleDate->format('Y-m-d'),
            'discount' => 0,
            'total_amount' => $subtotal > 0 ? $subtotal : $total,
            'payment_method' => 'credit',
            'payment_amount' => $paid,
            'change_amount' => 0,
            'debt_amount' => $remaining,
            'debt_status' => $status,
            'status' => $status === 'paid' ? 'completed' : 'pending',
            'user_id' => $kasir->id,
        ]);

        SaleItem::create([
            'sale_id' => $sale->id,
            'product_id' => $product->id,
            'quantity' => $qty,
            'unit_price' => $unitPrice,
            'subtotal' => $subtotal > 0 ? $subtotal : $total,
        ]);

        $this->recordSaleStockOut($sale, $product, $qty, $kasir->id, $saleDate->copy()->setTime($hour, $minute, 0));

        Debt::create([
            'sale_id' => $sale->id,
            'customer_name' => $row['name'],
            'customer_phone' => $row['phone'],
            'total_amount' => $subtotal > 0 ? $subtotal : $total,
            'paid_amount' => $paid,
            'remaining_amount' => $remaining,
            'due_date' => $dueDate->format('Y-m-d'),
            'status' => $status,
            'notes' => $debtNote,
            'user_id' => $kasir->id,
        ]);
    }

    private function recordSaleStockOut(Sale $sale, Product $product, int $qty, int $userId, Carbon $at): void
    {
        if ($qty < 1) {
            return;
        }

        $already = StockMovement::query()
            ->where('reference_type', 'sale')
            ->where('reference_id', $sale->id)
            ->where('product_id', $product->id)
            ->where('type', 'out')
            ->exists();

        if ($already) {
            return;
        }

        $product = Product::find($product->id) ?? $product;
        if ($product->stock_quantity < $qty) {
            $qty = max(1, (int) $product->stock_quantity);
        }

        $product->decrement('stock_quantity', $qty);

        $movement = StockMovement::create([
            'product_id' => $product->id,
            'type' => 'out',
            'quantity' => $qty,
            'reference_type' => 'sale',
            'reference_id' => $sale->id,
            'notes' => 'Sale transaction: '.$sale->invoice_number,
            'user_id' => $userId,
        ]);

        $movement->created_at = $at;
        $movement->updated_at = $at;
        $movement->saveQuietly();
    }

    public function syncMissingSaleStockMovements(?User $user = null): int
    {
        $user = $user
            ?? User::where('role', 'kasir')->first()
            ?? User::first();

        if (! $user) {
            return 0;
        }

        $synced = 0;

        $sales = Sale::query()
            ->with(['saleItems.product'])
            ->where(function ($q) {
                $q->where('payment_method', 'credit')
                    ->orWhereHas('debt')
                    ->orWhereIn('customer_name', array_merge(self::SEED_CUSTOMERS, self::WALK_IN_CUSTOMERS));
            })
            ->get();

        foreach ($sales as $sale) {
            $saleDate = Carbon::parse($sale->sale_date ?? $sale->created_at);
            $kasirId = $sale->user_id ?? $user->id;

            foreach ($sale->saleItems as $item) {
                $product = $item->product ?? Product::find($item->product_id);
                if (! $product) {
                    continue;
                }

                $exists = StockMovement::query()
                    ->where('reference_type', 'sale')
                    ->where('reference_id', $sale->id)
                    ->where('product_id', $product->id)
                    ->where('type', 'out')
                    ->exists();

                if ($exists) {
                    continue;
                }

                $this->recordSaleStockOut($sale, $product, (int) $item->quantity, $kasirId, $saleDate);
                $synced++;
            }
        }

        return $synced;
    }

    private function removePreviousSeedData(): void
    {
        $names = array_merge(self::SEED_CUSTOMERS, self::WALK_IN_CUSTOMERS);

        $saleIds = Sale::query()
            ->where(function ($q) use ($names) {
                $q->whereIn('customer_name', $names)
                    ->orWhere('invoice_number', 'like', 'INV-PIUTANG-%');
            })
            ->pluck('id');

        if ($saleIds->isEmpty()) {
            return;
        }

        DB::transaction(function () use ($saleIds) {
            $sales = Sale::with('saleItems')->whereIn('id', $saleIds)->get();

            foreach ($sales as $sale) {
                foreach ($sale->saleItems as $item) {
                    Product::where('id', $item->product_id)->increment('stock_quantity', (int) $item->quantity);
                }

                StockMovement::query()
                    ->where('reference_type', 'sale')
                    ->where('reference_id', $sale->id)
                    ->delete();
            }

            Debt::query()->whereIn('sale_id', $saleIds)->delete();
            Sale::query()->whereIn('id', $saleIds)->forceDelete();
        });
    }
}
