<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use App\Exports\SalesReportExport;
use App\Exports\StockReportExport;
use App\Exports\ProductReportExport;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ReportController extends Controller
{
    protected $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    /**
     * Download laporan penjualan dalam format PDF
     */
    public function downloadSalesPdf(Request $request)
    {
        $request->validate([
            'period' => 'required|in:daily,weekly,monthly',
            'date' => 'nullable|date',
            'user_id' => 'nullable|exists:users,id',
            'payment_method' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id'
        ]);

        $period = $request->period;
        $date = $request->date;
        $filters = $request->only(['user_id', 'payment_method', 'category_id']);

        // Generate data laporan
        $data = $this->reportService->generateSalesReport($period, $date, $filters);

        // Format data untuk view
        $periodText = [
            'daily' => 'Harian',
            'weekly' => 'Mingguan',
            'monthly' => 'Bulanan'
        ];

        // dateText untuk tampilan, fileDate untuk nama file (hindari slash/backslash di nama file)
        $dateText = $date ? Carbon::parse($date)->format('d/m/Y') : Carbon::now()->format('d/m/Y');
        $fileDate = $date ? Carbon::parse($date)->format('Ymd') : Carbon::now()->format('Ymd');

        try {
            // Generate PDF
            $pdf = Pdf::loadView('reports.sales-pdf', [
                'sales' => $data['sales'],
                'summary' => $data['summary'],
                'periodText' => $periodText[$period],
                'dateText' => $dateText
            ]);

            // Ensure filename doesn't contain disallowed characters
            $safeFileDate = preg_replace('/[^0-9A-Za-z_-]/', '', $fileDate);
            $filename = "laporan_penjualan_{$period}_{$safeFileDate}.pdf";

            return $pdf->download($filename);
        } catch (\Exception $e) {
            logger()->error('PDF generation failed for sales report: ' . $e->getMessage(), ['exception' => $e]);
            // If request expects JSON (AJAX) return JSON error, otherwise return a 500 with message
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['error' => 'Gagal membuat PDF: ' . $e->getMessage()], 500);
            }

            return response()->json(['error' => 'Gagal membuat PDF: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Download laporan penjualan dalam format Excel
     */
    public function downloadSalesExcel(Request $request)
    {
        $request->validate([
            'period' => 'required|in:daily,weekly,monthly',
            'date' => 'nullable|date',
            'user_id' => 'nullable|exists:users,id',
            'payment_method' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id'
        ]);

        $period = $request->period;
        $date = $request->date;
        $filters = $request->only(['user_id', 'payment_method', 'category_id']);

        // Generate data laporan
        $data = $this->reportService->generateSalesReport($period, $date, $filters);

        $dateText = $date ? Carbon::parse($date)->format('Y-m-d') : Carbon::now()->format('Y-m-d');
        $filename = "laporan_penjualan_{$period}_{$dateText}.xlsx";

        return Excel::download(new SalesReportExport($data, $period, $date), $filename);
    }

    /**
     * Download laporan stok dalam format PDF
     */
    public function downloadStockPdf(Request $request)
    {
        $request->merge([
            'product_id' => $request->filled('product_id') ? $request->product_id : null,
        ]);

        $request->validate([
            'period' => 'required|in:daily,weekly,monthly',
            'date' => 'nullable|date',
            'product_id' => 'nullable|exists:products,id',
        ]);

        $period = $request->period;
        $date = $request->date;
        $filters = $request->only(['product_id']);

        // Generate data laporan
        $data = $this->reportService->generateStockReport($period, $date, $filters);

        // dateText untuk tampilan, fileDate untuk nama file
        $dateText = $date ? Carbon::parse($date)->format('d/m/Y') : Carbon::now()->format('d/m/Y');
        $fileDate = $date ? Carbon::parse($date)->format('Ymd') : Carbon::now()->format('Ymd');
        try {
            $safeFileDate = preg_replace('/[^0-9A-Za-z_-]/', '', $fileDate);
            $filename = "laporan_stok_{$period}_{$safeFileDate}.pdf";

            // Generate PDF menggunakan view yang sudah ada atau buat view baru
            $pdf = Pdf::loadView('reports.stock-pdf', [
                'movements' => $data['movements'],
                'summary' => $data['summary'],
                'period' => $period,
                'dateText' => $dateText
            ]);

            return $pdf->download($filename);
        } catch (\Exception $e) {
            logger()->error('PDF generation failed for stock report: ' . $e->getMessage(), ['exception' => $e]);
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['error' => 'Gagal membuat PDF stok: ' . $e->getMessage()], 500);
            }
            return response()->json(['error' => 'Gagal membuat PDF stok: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Download laporan stok dalam format Excel
     */
    public function downloadStockExcel(Request $request)
    {
        $request->merge([
            'product_id' => $request->filled('product_id') ? $request->product_id : null,
        ]);

        $request->validate([
            'period' => 'required|in:daily,weekly,monthly',
            'date' => 'nullable|date',
            'product_id' => 'nullable|exists:products,id',
        ]);

        $period = $request->period;
        $date = $request->date;
        $filters = $request->only(['product_id']);

        // Generate data laporan
        $data = $this->reportService->generateStockReport($period, $date, $filters);

        $dateText = $date ? Carbon::parse($date)->format('Y-m-d') : Carbon::now()->format('Y-m-d');
        $filename = "laporan_stok_{$period}_{$dateText}.xlsx";

        return Excel::download(new StockReportExport($data, $period, $date), $filename);
    }

    /**
     * Download laporan produk dalam format Excel
     */
    public function downloadProductExcel(Request $request)
    {
        $request->validate([
            'period' => 'required|in:daily,weekly,monthly',
            'date' => 'nullable|date',
            'category_id' => 'nullable|exists:categories,id'
        ]);

        $period = $request->period;
        $date = $request->date;
        $filters = $request->only(['category_id']);

        // Generate data laporan
        $data = $this->reportService->generateProductReport($period, $date, $filters);

        $dateText = $date ? Carbon::parse($date)->format('Y-m-d') : Carbon::now()->format('Y-m-d');
        $filename = "laporan_produk_{$period}_{$dateText}.xlsx";

        return Excel::download(new ProductReportExport($data, $period, $date), $filename);
    }

    /**
     * Get preview data laporan (untuk testing)
     */
    public function previewSalesReport(Request $request)
    {
        $request->validate([
            'period' => 'required|in:daily,weekly,monthly',
            'date' => 'nullable|date',
            'user_id' => 'nullable|exists:users,id',
            'payment_method' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id'
        ]);

        $period = $request->period;
        $date = $request->date;
        $filters = $request->only(['user_id', 'payment_method', 'category_id']);

        $data = $this->reportService->generateSalesReport($period, $date, $filters);

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Get preview data laporan stok
     */
    public function previewStockReport(Request $request)
    {
        $request->validate([
            'period' => 'required|in:daily,weekly,monthly',
            'date' => 'nullable|date',
            'product_id' => 'nullable|exists:products,id'
        ]);

        $period = $request->period;
        $date = $request->date;
        $filters = $request->only(['product_id']);

        $data = $this->reportService->generateStockReport($period, $date, $filters);

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Laporan penjualan untuk web interface
     */
    public function salesReport(Request $request)
    {
        try {
            $type = $request->get('type', 'daily');
            $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
            $endDate = $request->get('end_date', now()->endOfMonth()->format('Y-m-d'));

            $query = Sale::with(['saleItems.product', 'user']);

            if ($type === 'daily') {
                $query->whereBetween('sale_date', [$startDate, $endDate])
                    ->selectRaw('DATE(sale_date) as date, COUNT(*) as total_transactions, SUM(total_amount) as total_sales')
                    ->groupBy('date')
                    ->orderBy('date');
            } elseif ($type === 'monthly') {
                $query->whereBetween('sale_date', [$startDate, $endDate])
                    ->selectRaw('YEAR(sale_date) as year, MONTH(sale_date) as month, COUNT(*) as total_transactions, SUM(total_amount) as total_sales')
                    ->groupBy('year', 'month')
                    ->orderBy('year')
                    ->orderBy('month');
            }

            $report = $query->get();

            // Summary
            $summary = Sale::whereBetween('sale_date', [$startDate, $endDate])
                ->selectRaw('COUNT(*) as total_transactions, SUM(total_amount) as total_sales, AVG(total_amount) as average_sale')
                ->first();

            return response()->json([
                'type' => $type,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'summary' => $summary,
                'data' => $report
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Terjadi kesalahan saat memuat laporan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Laporan stok untuk web interface (pergerakan stok + ringkasan).
     */
    public function stockReport(Request $request)
    {
        try {
            $period = $request->get('period');
            $date = $request->get('date');
            $productId = $request->get('product_id');
            $type = $request->get('type');

            $query = StockMovement::with(['product', 'user']);

            if ($period && in_array($period, ['daily', 'weekly', 'monthly'], true)) {
                $targetDate = $date ? Carbon::parse($date) : Carbon::now();
                if ($period === 'daily') {
                    $query->whereDate('stock_movements.created_at', $targetDate->toDateString());
                } elseif ($period === 'weekly') {
                    $query->whereBetween('stock_movements.created_at', [
                        $targetDate->copy()->startOfWeek(),
                        $targetDate->copy()->endOfWeek(),
                    ]);
                } else {
                    $query->whereBetween('stock_movements.created_at', [
                        $targetDate->copy()->startOfMonth(),
                        $targetDate->copy()->endOfMonth(),
                    ]);
                }
            }

            if ($productId) {
                $query->where('stock_movements.product_id', $productId);
            }

            if ($type && in_array($type, ['in', 'out'], true)) {
                $query->where('stock_movements.type', $type);
            }

            $movements = $query->orderByDesc('stock_movements.created_at')
                ->orderByDesc('stock_movements.id')
                ->get();

            $this->reportService->enrichMovementsWithRunningStock($movements);

            $summary = [
                'total_movements' => $movements->count(),
                'total_in' => $movements->where('type', 'in')->sum('quantity'),
                'total_out' => $movements->where('type', 'out')->sum('quantity'),
                'net_change' => $movements->where('type', 'in')->sum('quantity')
                    - $movements->where('type', 'out')->sum('quantity'),
            ];

            return response()->json([
                'summary' => $summary,
                'data' => $movements,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Terjadi kesalahan saat memuat laporan stok: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Alias kompatibilitas untuk route /stock-movement
     */
    public function stockMovementReport(Request $request)
    {
        return $this->stockReport($request);
    }

    /**
     * List semua transaksi penjualan untuk halaman laporan
     */
    public function salesList(Request $request)
    {
        try {
            $startDate = $request->query('start_date');
            $endDate = $request->query('end_date');
            $productQuery = trim((string) $request->query('product', ''));
            $customerQuery = trim((string) $request->query('customer', ''));
            $showDeleted = filter_var($request->query('show_deleted', false), FILTER_VALIDATE_BOOL);
            $showCancelled = filter_var($request->query('show_cancelled', false), FILTER_VALIDATE_BOOL);
            $perPage = (int) $request->query('per_page', 10);
            if ($perPage < 5) $perPage = 5;
            if ($perPage > 50) $perPage = 50;

            $query = Sale::with(['user', 'saleItems.product'])
                ->when(!$showDeleted, function ($q) {
                    $q->where('status', '!=', 'deleted');
                })
                ->when(!$showCancelled, function ($q) {
                    $q->where('status', '!=', 'cancelled');
                })
                ->when($startDate && $endDate, function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('sale_date', [
                        Carbon::parse($startDate)->startOfDay(),
                        Carbon::parse($endDate)->endOfDay(),
                    ]);
                })
                ->when($customerQuery !== '', function ($q) use ($customerQuery) {
                    $q->where('customer_name', 'like', '%' . $customerQuery . '%');
                })
                ->when($productQuery !== '', function ($q) use ($productQuery) {
                    $q->whereHas('saleItems.product', function ($sub) use ($productQuery) {
                        $sub->where('name', 'like', '%' . $productQuery . '%');
                    });
                })
                ->orderByDesc('sale_date')
                ->orderByDesc('id');

            $paginator = $query->paginate($perPage);
            $items = $paginator->getCollection()->map(function ($sale) {
                $productNames = $sale->saleItems
                    ->map(fn($i) => $i->product->name ?? null)
                    ->filter()
                    ->unique()
                    ->values()
                    ->take(3)
                    ->implode(', ');

                    return [
                        'id' => $sale->id,
                        'sale_date' => $sale->sale_date ?? $sale->created_at,
                        'customer_name' => $sale->customer_name ?? '-',
                        'total_amount' => $sale->total_amount ?? 0,
                        'discount' => $sale->discount ?? 0,
                        'payment_amount' => $sale->payment_amount ?? 0,
                        'change_amount' => $sale->change_amount ?? 0,
                        'payment_method' => $sale->payment_method ?? '-',
                        'status' => $sale->status ?? 'completed',
                        'user_name' => $sale->user->name ?? '-',
                    'items_count' => $sale->saleItems->count(),
                    'products' => $productNames,
                    ];
                });

            $paginator->setCollection($items);

            return response()->json([
                'data' => $paginator->items(),
                'meta' => [
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                    'from' => $paginator->firstItem(),
                    'to' => $paginator->lastItem(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Terjadi kesalahan saat memuat data penjualan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ringkasan statistik + grafik untuk laporan penjualan
     * Query params:
     * - start_date=YYYY-MM-DD
     * - end_date=YYYY-MM-DD
     */
    public function salesAnalytics(Request $request)
    {
        try {
            $startDate = $request->query('start_date');
            $endDate = $request->query('end_date');
            $productQuery = trim((string) $request->query('product', ''));
            $customerQuery = trim((string) $request->query('customer', ''));
            $showDeleted = filter_var($request->query('show_deleted', false), FILTER_VALIDATE_BOOL);
            $showCancelled = filter_var($request->query('show_cancelled', false), FILTER_VALIDATE_BOOL);

            // Default: bulan ini
            $start = $startDate ? Carbon::parse($startDate)->startOfDay() : now()->startOfMonth();
            $end = $endDate ? Carbon::parse($endDate)->endOfDay() : now()->endOfMonth();

            $base = Sale::query()
                ->whereBetween('sale_date', [$start, $end])
                ->when(!$showDeleted, function ($q) {
                    $q->where('status', '!=', 'deleted');
                })
                ->when(!$showCancelled, function ($q) {
                    $q->where('status', '!=', 'cancelled');
                })
                ->when($customerQuery !== '', function ($q) use ($customerQuery) {
                    $q->where('customer_name', 'like', '%' . $customerQuery . '%');
                })
                ->when($productQuery !== '', function ($q) use ($productQuery) {
                    $q->whereHas('saleItems.product', function ($sub) use ($productQuery) {
                        $sub->where('name', 'like', '%' . $productQuery . '%');
                    });
                });

            $summary = (clone $base)->selectRaw('COUNT(*) as total_transactions, SUM(total_amount) as total_sales')->first();

            $dailyRows = (clone $base)
                ->selectRaw('DATE(sale_date) as d, COUNT(*) as total_transactions, SUM(total_amount) as total_sales')
                ->groupBy('d')
                ->orderBy('d')
                ->get();

            // Buat rentang tanggal lengkap (agar tanggal tanpa transaksi tetap muncul sebagai 0)
            $dailyMap = $dailyRows->keyBy(fn ($r) => (string) $r->d);
            $daily = collect();
            $cursor = $start->copy()->startOfDay();
            $endCursor = $end->copy()->startOfDay();
            while ($cursor->lte($endCursor)) {
                $key = $cursor->toDateString();
                $row = $dailyMap->get($key);
                $daily->push([
                    'date' => $key,
                    'total_transactions' => (int) ($row->total_transactions ?? 0),
                    'total_sales' => (float) ($row->total_sales ?? 0),
                ]);
                $cursor->addDay();
            }

            $paymentMethods = (clone $base)
                ->selectRaw('COALESCE(payment_method, "unknown") as method, COUNT(*) as c, SUM(total_amount) as total_amount')
                ->groupBy('method')
                ->orderByDesc('total_amount')
                ->get()
                ->map(fn ($r) => [
                    'method' => (string) $r->method,
                    'count' => (int) ($r->c ?? 0),
                    'amount' => (float) ($r->total_amount ?? 0),
                ]);

            // Piutang (credit)
            $creditTotal = (clone $base)->where('payment_method', 'credit')->sum('total_amount');

            // Uang langsung (bukan piutang / kredit)
            $cashDirectTotal = (clone $base)
                ->where(function ($q) {
                    $q->whereNull('payment_method')->orWhere('payment_method', '!=', 'credit');
                })
                ->sum('total_amount');

            // Jumlah transaksi dibatalkan (tetap ditampilkan agar petugas sadar, terpisah dari filter "tampilkan")
            $cancelledCount = Sale::query()
                ->whereBetween('sale_date', [$start, $end])
                ->when(!$showDeleted, function ($q) {
                    $q->where('status', '!=', 'deleted');
                })
                ->where('status', 'cancelled')
                ->when($customerQuery !== '', function ($q) use ($customerQuery) {
                    $q->where('customer_name', 'like', '%' . $customerQuery . '%');
                })
                ->when($productQuery !== '', function ($q) use ($productQuery) {
                    $q->whereHas('saleItems.product', function ($sub) use ($productQuery) {
                        $sub->where('name', 'like', '%' . $productQuery . '%');
                    });
                })
                ->count();

            $topProducts = SaleItem::query()
                ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
                ->join('products', 'sale_items.product_id', '=', 'products.id')
                ->whereBetween('sales.sale_date', [$start, $end])
                ->when(!$showDeleted, function ($q) {
                    $q->where('sales.status', '!=', 'deleted');
                })
                ->when(!$showCancelled, function ($q) {
                    $q->where('sales.status', '!=', 'cancelled');
                })
                ->when($customerQuery !== '', function ($q) use ($customerQuery) {
                    $q->where('sales.customer_name', 'like', '%' . $customerQuery . '%');
                })
                ->when($productQuery !== '', function ($q) use ($productQuery) {
                    $q->where('products.name', 'like', '%' . $productQuery . '%');
                })
                ->selectRaw('products.name as name, SUM(sale_items.quantity) as qty')
                ->groupBy('products.id', 'products.name')
                ->orderByDesc('qty')
                ->limit(10)
                ->get()
                ->map(fn ($r) => [
                    'name' => (string) $r->name,
                    'qty' => (int) $r->qty,
                ]);

            return response()->json([
                'start_date' => $start->toDateString(),
                'end_date' => $end->toDateString(),
                'summary' => [
                    'total_transactions' => (int) ($summary->total_transactions ?? 0),
                    'total_sales' => (float) ($summary->total_sales ?? 0),
                    'total_credit' => (float) ($creditTotal ?? 0),
                    'cash_direct' => (float) ($cashDirectTotal ?? 0),
                    'cancelled_count' => (int) $cancelledCount,
                ],
                'charts' => [
                    'daily' => $daily,
                    'payment_methods' => $paymentMethods,
                    'top_products' => $topProducts,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Terjadi kesalahan saat memuat statistik: ' . $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Detail transaksi penjualan
     */
    public function salesDetail($id)
    {
        try {
            $sale = Sale::with(['saleItems.product', 'user'])->findOrFail($id);

            return response()->json([
                'data' => [
                    'id' => $sale->id,
                    'sale_date' => $sale->sale_date ?? $sale->created_at,
                    'customer_name' => $sale->customer_name ?? '-',
                    'customer_phone' => $sale->customer_phone ?? '',
                    'discount' => $sale->discount ?? 0,
                    'payment_amount' => $sale->payment_amount ?? 0,
                    'change_amount' => $sale->change_amount ?? 0,
                    'total_amount' => $sale->total_amount ?? 0,
                    'payment_method' => $sale->payment_method ?? '-',
                    'status' => $sale->status ?? 'completed',
                    'user_name' => $sale->user->name ?? '-',
                    'items' => $sale->saleItems->map(function ($item) {
                        return [
                            'product_name' => $item->product->name ?? '-',
                            'unit_price' => $item->unit_price ?? 0,
                            'quantity' => $item->quantity ?? 0,
                            'subtotal' => $item->subtotal ?? 0,
                        ];
                    }),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Terjadi kesalahan saat memuat detail penjualan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update transaksi penjualan (metadata)
     */
    public function updateSale(Request $request, $id)
    {
        $request->validate([
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:50',
            'discount' => 'nullable|numeric|min:0',
            'payment_amount' => 'nullable|numeric|min:0',
            'change_amount' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|string',
            'status' => 'nullable|string'
        ]);

        try {
            $sale = Sale::findOrFail($id);

            $sale->fill($request->only([
                'customer_name',
                'customer_phone',
                'discount',
                'payment_amount',
                'change_amount',
                'payment_method',
                'status'
            ]));

            $sale->save();

            return response()->json(['success' => true, 'data' => $sale]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Gagal mengupdate transaksi: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Soft delete transaksi penjualan tanpa menghapus data POS
     */
    public function deleteSale($id)
    {
        try {
            DB::beginTransaction();

            $sale = Sale::findOrFail($id);

            // Update status to 'deleted' and soft delete the record
            $sale->status = 'deleted';
            $sale->save();
            $sale->delete(); // This will set deleted_at timestamp

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil dihapus',
                'data' => null
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Delete sale error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus transaksi: ' . $e->getMessage()
            ], 500);
        }
    }
}
