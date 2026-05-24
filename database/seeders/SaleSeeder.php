<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
class SaleSeeder extends Seeder
{
    public function run(): void
    {
        $kasir = User::where('email', 'kasir@example.com')->first()
            ?? User::where('role', 'kasir')->first()
            ?? User::first();

        if (! $kasir) {
            $this->command?->warn('SaleSeeder: tidak ada user.');

            return;
        }

        $products = Product::all();
        if ($products->isEmpty()) {
            $this->command?->warn('SaleSeeder: tidak ada produk. Jalankan ProductSeeder dulu.');

            return;
        }

        // Hapus transaksi demo lama agar tidak duplikat saat seed ulang
        Sale::query()->where('invoice_number', 'like', 'INV-DEMO-%')->forceDelete();

        $salesData = $this->buildSalesSchedule();

        $seq = 1;
        foreach ($salesData as $saleData) {
            $product = $products->firstWhere('barcode', $saleData['barcode'])
                ?? $products->random();

            $qty = $saleData['qty'];
            $unitPrice = (float) $product->price;
            $subtotal = $qty * $unitPrice;
            $discount = (float) ($saleData['discount'] ?? 0);
            $total = max(0, $subtotal - $discount);
            $payment = (float) ($saleData['payment'] ?? $total);
            $change = max(0, $payment - $total);

            $sale = Sale::create([
                'invoice_number' => 'INV-DEMO-'.str_pad((string) $seq++, 5, '0', STR_PAD_LEFT),
                'customer_name' => $saleData['customer'],
                'customer_phone' => $saleData['phone'] ?? null,
                'sale_date' => $saleData['date'],
                'discount' => $discount,
                'total_amount' => $total,
                'payment_method' => $saleData['payment_method'],
                'payment_amount' => $payment,
                'change_amount' => $change,
                'status' => 'completed',
                'user_id' => $kasir->id,
            ]);

            SaleItem::create([
                'sale_id' => $sale->id,
                'product_id' => $product->id,
                'quantity' => $qty,
                'unit_price' => $unitPrice,
                'subtotal' => $subtotal,
            ]);
        }

        $this->command?->info('SaleSeeder: '.count($salesData).' transaksi demo dibuat.');
    }

    /** @return list<array<string, mixed>> */
    private function buildSalesSchedule(): array
    {
        $today = Carbon::today();
        $rows = [];

        // 3 bulan ke belakang + bulan berjalan (untuk grafik & laporan)
        for ($m = 3; $m >= 0; $m--) {
            $month = $today->copy()->subMonths($m);
            $days = [3, 7, 12, 18, 25];
            if ($m === 0) {
                $days = array_filter($days, fn ($d) => $d <= $today->day);
                if ($today->day >= 1) {
                    $days[] = max(1, (int) $today->day);
                }
                $days = array_unique($days);
                sort($days);
            }

            foreach ($days as $day) {
                if ($day > $month->daysInMonth) {
                    continue;
                }
                $date = $month->copy()->day($day)->format('Y-m-d');
                $rows[] = $this->row($date, 'Pelanggan '.$month->format('M').'-'.$day, '0812'.random_int(1000000, 9999999), 'BRG-004', rand(2, 8), 'cash');
            }
        }

        // Transaksi besar (mirip data uji laporan)
        $rows[] = $this->row($today->copy()->subDays(8)->format('Y-m-d'), 'PT Tani Makmur', '081398765432', 'BRG-004', 95, 'cash', 0);
        $rows[] = $this->row($today->copy()->subDays(8)->format('Y-m-d'), 'Koperasi Subur', '081377712345', 'BRG-005', 4, 'cash', 0);
        $rows[] = $this->row($today->copy()->subDays(5)->format('Y-m-d'), 'Pak Budi', '081355566677', 'BRG-001', 2, 'transfer', 0);
        $rows[] = $this->row($today->copy()->subDays(3)->format('Y-m-d'), 'Bu Siti', '081344455566', 'BRG-002', 3, 'cash', 5000);
        $rows[] = $this->row($today->copy()->subDays(2)->format('Y-m-d'), 'Pak Ahmad', '081333344455', 'BRG-003', 1, 'credit', 0);
        $rows[] = $this->row($today->format('Y-m-d'), 'Walk-in', null, 'BRG-006', 2, 'cash', 0);

        return $rows;
    }

    private function row(
        string $date,
        string $customer,
        ?string $phone,
        string $barcode,
        int $qty,
        string $paymentMethod,
        float $discount = 0
    ): array {
        return [
            'date' => $date,
            'customer' => $customer,
            'phone' => $phone,
            'barcode' => $barcode,
            'qty' => $qty,
            'payment_method' => $paymentMethod,
            'discount' => $discount,
        ];
    }
}
