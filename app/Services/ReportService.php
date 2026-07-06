<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportService
{
    /**
     * Generate laporan penjualan berdasarkan periode
     */
    public function generateSalesReport($period = null, $date = null, $filters = [])
    {
        // Eager-load the defined relationship `saleItems` (avoid relying on alias)
        $query = Sale::with(['saleItems.product', 'user'])
            ->select([
                'sales.*',
                DB::raw('SUM(sale_items.quantity) as total_items'),
                DB::raw('SUM(sale_items.quantity * sale_items.unit_price) as total_amount'),
            ])
            ->join('sale_items', 'sales.id', '=', 'sale_items.sale_id')
            ->groupBy('sales.id');

        $startDate = $filters['start_date'] ?? null;
        $endDate = $filters['end_date'] ?? null;
        $useRange = $startDate && $endDate && ! $period;

        if ($useRange) {
            $query->whereBetween('sales.sale_date', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay(),
            ]);
        } elseif ($period) {
            $anchorDate = $date ?? $startDate;
            $this->applyPeriodFilter($query, $period, $anchorDate);
        }

        $this->applyAdditionalFilters($query, $filters);

        $sales = $query->orderByDesc('sales.sale_date')->orderByDesc('sales.id')->get();

        // Hitung ringkasan
        $summary = $this->calculateSalesSummary($sales);

        return [
            'sales' => $sales,
            'summary' => $summary,
            'period' => $period,
            'date' => $date,
            'filters' => $filters,
        ];
    }

    /**
     * Generate laporan stok berdasarkan periode
     */
    public function generateStockReport($period, $date = null, $filters = [])
    {
        try {
            $query = StockMovement::with(['product', 'user'])
                ->select([
                    'stock_movements.*',
                    'products.name as product_name',
                    'products.stock_quantity as current_stock'
                ])
                ->join('products', 'stock_movements.product_id', '=', 'products.id');

            // Filter berdasarkan periode (opsional — kosong = semua data)
            if ($period) {
                $this->applyPeriodFilter($query, $period, $date);
            }

            // Filter tambahan
            if (! empty($filters['product_id'])) {
                $query->where('stock_movements.product_id', $filters['product_id']);
            }

            if (! empty($filters['type']) && in_array($filters['type'], ['in', 'out'], true)) {
                $query->where('stock_movements.type', $filters['type']);
            }

            $movements = $query->orderByDesc('stock_movements.created_at')
                ->orderByDesc('stock_movements.id')
                ->get();
            $this->enrichMovementsWithRunningStock($movements);

            // Hitung ringkasan stok
            $summary = $this->calculateStockSummary($movements);

            return [
                'movements' => $movements,
                'summary' => $summary,
                'period' => $period,
                'date' => $date,
                'filters' => $filters
            ];
        } catch (\Exception $e) {
            // Fallback: return empty data jika ada error
            return [
                'movements' => collect(),
                'summary' => [
                    'total_in' => 0,
                    'total_out' => 0,
                    'net_change' => 0,
                    'total_movements' => 0
                ],
                'period' => $period,
                'date' => $date,
                'filters' => $filters
            ];
        }
    }

    /**
     * Generate laporan produk berdasarkan periode
     */
    public function generateProductReport($period, $date = null, $filters = [])
    {
        try {
            $query = Product::with(['category', 'supplier'])
                ->select([
                    'products.*',
                    DB::raw('COALESCE(SUM(sale_items.quantity), 0) as total_sold'),
                    DB::raw('COALESCE(SUM(sale_items.quantity * sale_items.unit_price), 0) as total_revenue')
                ])
                ->leftJoin('sale_items', 'products.id', '=', 'sale_items.product_id')
                ->leftJoin('sales', 'sale_items.sale_id', '=', 'sales.id');

            // Filter berdasarkan periode - gunakan sales.sale_date untuk filter periode
            if ($period === 'daily') {
                $targetDate = $date ? Carbon::parse($date) : Carbon::now();
                $query->whereDate('sales.sale_date', $targetDate->toDateString());
            } elseif ($period === 'weekly') {
                $targetDate = $date ? Carbon::parse($date) : Carbon::now();
                $startOfWeek = $targetDate->copy()->startOfWeek();
                $endOfWeek = $targetDate->copy()->endOfWeek();
                $query->whereBetween('sales.sale_date', [$startOfWeek, $endOfWeek]);
            } elseif ($period === 'monthly') {
                $targetDate = $date ? Carbon::parse($date . '-01') : Carbon::now();
                $startOfMonth = $targetDate->copy()->startOfMonth();
                $endOfMonth = $targetDate->copy()->endOfMonth();
                $query->whereBetween('sales.sale_date', [$startOfMonth, $endOfMonth]);
            }

            // Filter tambahan
            if (isset($filters['category_id'])) {
                $query->where('products.category_id', $filters['category_id']);
            }

            $products = $query->groupBy('products.id')->get();

            // Hitung ringkasan produk
            $summary = $this->calculateProductSummary($products);

            return [
                'products' => $products,
                'summary' => $summary,
                'period' => $period,
                'date' => $date,
                'filters' => $filters
            ];
        } catch (\Exception $e) {
            // Fallback: return empty data jika ada error
            return [
                'products' => collect(),
                'summary' => [
                    'total_products' => 0,
                    'total_stock' => 0,
                    'total_revenue' => 0,
                    'total_sold' => 0,
                    'average_price' => 0
                ],
                'period' => $period,
                'date' => $date,
                'filters' => $filters
            ];
        }
    }

    /**
     * Terapkan filter periode pada query
     */
    private function applyPeriodFilter($query, $period, $date = null)
    {
        $targetDate = $date ? Carbon::parse($date) : Carbon::now();
        $model = $query->getModel();

        if ($model instanceof \App\Models\Sale) {
            $dateCol = 'sale_date';
        } elseif ($model instanceof \App\Models\StockMovement) {
            $dateCol = 'stock_movements.created_at';
        } else {
            $dateCol = 'created_at';
        }

        switch ($period) {
            case 'daily':
                $query->whereDate($dateCol, $targetDate->toDateString());
                break;

            case 'weekly':
                $query->whereBetween($dateCol, [
                    $targetDate->copy()->startOfWeek(),
                    $targetDate->copy()->endOfWeek(),
                ]);
                break;

            case 'monthly':
                $query->whereBetween($dateCol, [
                    $targetDate->copy()->startOfMonth(),
                    $targetDate->copy()->endOfMonth(),
                ]);
                break;
        }
    }

    /**
     * Terapkan filter tambahan
     */
    private function applyAdditionalFilters($query, $filters)
    {
        $showDeleted = filter_var($filters['show_deleted'] ?? false, FILTER_VALIDATE_BOOL);
        $showCancelled = filter_var($filters['show_cancelled'] ?? false, FILTER_VALIDATE_BOOL);

        if (! $showDeleted) {
            $query->where('sales.status', '!=', 'deleted');
        }

        if (! $showCancelled) {
            $query->where('sales.status', '!=', 'cancelled');
        }

        if (! empty($filters['user_id'])) {
            $query->where('sales.user_id', $filters['user_id']);
        }

        if (! empty($filters['payment_method'])) {
            $query->where('sales.payment_method', $filters['payment_method']);
        }

        if (! empty($filters['category_id'])) {
            $query->whereHas('saleItems.product', function ($q) use ($filters) {
                $q->where('category_id', $filters['category_id']);
            });
        }

        if (! empty($filters['product'])) {
            $product = trim((string) $filters['product']);
            $query->whereHas('saleItems.product', function ($q) use ($product) {
                $q->where('name', 'like', '%'.$product.'%');
            });
        }

        if (! empty($filters['customer'])) {
            $customer = trim((string) $filters['customer']);
            $query->where('sales.customer_name', 'like', '%'.$customer.'%');
        }
    }

    /**
     * Hitung ringkasan penjualan
     */
    private function calculateSalesSummary($sales)
    {
        $totalSales = $sales->count();
        $totalAmount = $sales->sum('total_amount');
        $totalItems = $sales->sum('total_items');
        $averageTransaction = $totalSales > 0 ? $totalAmount / $totalSales : 0;

        // Top products
        $topProducts = collect();
        foreach ($sales as $sale) {
            // Use the defined relation saleItems to avoid relation name issues
            foreach ($sale->saleItems as $item) {
                $existing = $topProducts->firstWhere('product_id', $item->product_id);
                if ($existing) {
                    $existing['quantity'] += $item->quantity;
                    $existing['revenue'] += $item->quantity * $item->unit_price;
                } else {
                    $topProducts->push([
                        'product_id' => $item->product_id,
                        'product_name' => $item->product->name ?? '-',
                        'quantity' => $item->quantity,
                        'revenue' => $item->quantity * $item->unit_price
                    ]);
                }
            }
        }

        $topProducts = $topProducts->sortByDesc('quantity')->take(5);

        return [
            'total_sales' => $totalSales,
            'total_amount' => $totalAmount,
            'total_items' => $totalItems,
            'average_transaction' => round($averageTransaction, 2),
            'top_products' => $topProducts->values()
        ];
    }

    /**
     * Hitung stok sebelum/sesudah per baris berdasarkan stok saat ini dan seluruh riwayat pergerakan.
     */
    public function enrichMovementsWithRunningStock($movements): void
    {
        if ($movements->isEmpty()) {
            return;
        }

        $productIds = $movements->pluck('product_id')->unique()->filter()->values();
        if ($productIds->isEmpty()) {
            return;
        }

        $stocks = Product::whereIn('id', $productIds)->pluck('stock_quantity', 'id');

        $nets = StockMovement::query()
            ->whereIn('product_id', $productIds)
            ->selectRaw('product_id, SUM(CASE WHEN type = ? THEN quantity ELSE -quantity END) as net', ['in'])
            ->groupBy('product_id')
            ->pluck('net', 'product_id');

        $allByProduct = StockMovement::query()
            ->whereIn('product_id', $productIds)
            ->orderBy('created_at')
            ->orderBy('id')
            ->get()
            ->groupBy('product_id');

        $meta = [];

        foreach ($productIds as $pid) {
            $initial = (int) ($stocks[$pid] ?? 0) - (int) ($nets[$pid] ?? 0);
            $balance = $initial;

            foreach ($allByProduct->get($pid, collect()) as $m) {
                if ($m->type === 'in') {
                    $before = $balance;
                    $balance += (int) $m->quantity;
                    $after = $balance;
                } else {
                    $before = $balance;
                    $balance -= (int) $m->quantity;
                    $after = $balance;
                }
                $meta[$m->id] = ['stock_before' => $before, 'stock_after' => $after];
            }
        }

        foreach ($movements as $m) {
            if (isset($meta[$m->id])) {
                $m->setAttribute('stock_before', $meta[$m->id]['stock_before']);
                $m->setAttribute('stock_after', $meta[$m->id]['stock_after']);
            } else {
                $m->setAttribute('stock_before', 0);
                $m->setAttribute('stock_after', 0);
            }
        }
    }

    /**
     * Hitung ringkasan stok
     */
    private function calculateStockSummary($movements)
    {
        $totalIn = $movements->where('type', 'in')->sum('quantity');
        $totalOut = $movements->where('type', 'out')->sum('quantity');
        $netChange = $totalIn - $totalOut;

        return [
            'total_in' => $totalIn,
            'total_out' => $totalOut,
            'net_change' => $netChange,
            'total_movements' => $movements->count()
        ];
    }

    /**
     * Hitung ringkasan produk
     */
    private function calculateProductSummary($products)
    {
        $totalProducts = $products->count();
        $totalStock = $products->sum('stock_quantity');
        $totalRevenue = $products->sum('total_revenue');
        $totalSold = $products->sum('total_sold');

        return [
            'total_products' => $totalProducts,
            'total_stock' => $totalStock,
            'total_revenue' => $totalRevenue,
            'total_sold' => $totalSold,
            'average_price' => $totalProducts > 0 ? $totalRevenue / $totalProducts : 0
        ];
    }
}
