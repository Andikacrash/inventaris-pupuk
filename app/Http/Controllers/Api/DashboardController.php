<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Debt;
use App\Models\Product;
use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Get dashboard summary
     */
    public function summary()
    {
        try {
            // Total products
            $totalProducts = Product::count();

            // Total stock value
            $totalStockValue = Product::sum(DB::raw('price * stock_quantity'));

            // Products with low stock
            $lowStockProducts = Product::whereColumn('stock_quantity', '<=', 'minimum_stock')->count();

            // Today's sales
            $todaySales = Sale::whereDate('sale_date', today())->sum('total_amount');

            // This month's sales
            $monthSales = Sale::whereMonth('sale_date', now()->month)
                ->whereYear('sale_date', now()->year)
                ->sum('total_amount');

            // Total sales count today
            $todaySalesCount = Sale::whereDate('sale_date', today())->count();
            $activeReceivables = Debt::whereIn('status', ['unpaid', 'partial'])->sum('remaining_amount');

            return response()->json([
                'total_products' => $totalProducts,
                'total_stock_value' => number_format($totalStockValue, 2),
                'low_stock_products' => $lowStockProducts,
                'today_sales' => number_format($todaySales, 2),
                'month_sales' => number_format($monthSales, 2),
                'today_sales_count' => $todaySalesCount,
                'active_receivables' => number_format($activeReceivables, 2),
                'is_admin' => Auth::check() ? (Auth::user()->role === 'admin') : false,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'total_products' => 0,
                'total_stock_value' => '0.00',
                'low_stock_products' => 0,
                'today_sales' => '0.00',
                'month_sales' => '0.00',
                'today_sales_count' => 0,
                'active_receivables' => '0.00',
                'is_admin' => false,
            ], 500);
        }
    }

    /**
     * Get sales chart data (last 6 months)
     */
    public function salesChart()
    {
        $salesData = Sale::selectRaw('MONTH(sale_date) as month, YEAR(sale_date) as year, SUM(total_amount) as total')
            ->whereBetween('sale_date', [
                now()->subMonths(5)->startOfMonth(),
                now()->endOfMonth(),
            ])
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        $chartData = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthData = $salesData->where('month', $date->month)->where('year', $date->year)->first();

            $chartData[] = [
                'month' => $date->format('M Y'),
                'total' => $monthData ? number_format($monthData->total, 2) : '0.00',
            ];
        }

        return response()->json($chartData);
    }

    /**
     * Get sales comparison data (today, 3 months ago, 6 months ago)
     */
    public function salesComparison()
    {
        // Today's sales
        $todaySales = Sale::whereDate('sale_date', today())->sum('total_amount');

        // 3 months ago sales
        $threeMonthsAgo = now()->subMonths(3);
        $threeMonthsAgoSales = Sale::whereMonth('sale_date', $threeMonthsAgo->month)
            ->whereYear('sale_date', $threeMonthsAgo->year)
            ->sum('total_amount');

        // 6 months ago sales
        $sixMonthsAgo = now()->subMonths(6);
        $sixMonthsAgoSales = Sale::whereMonth('sale_date', $sixMonthsAgo->month)
            ->whereYear('sale_date', $sixMonthsAgo->year)
            ->sum('total_amount');

        return response()->json([
            'today' => [
                'period' => 'Hari Ini',
                'amount' => number_format($todaySales, 2),
                'raw_amount' => $todaySales,
            ],
            'three_months_ago' => [
                'period' => '3 Bulan Lalu',
                'amount' => number_format($threeMonthsAgoSales, 2),
                'raw_amount' => $threeMonthsAgoSales,
            ],
            'six_months_ago' => [
                'period' => '6 Bulan Lalu',
                'amount' => number_format($sixMonthsAgoSales, 2),
                'raw_amount' => $sixMonthsAgoSales,
            ],
        ]);
    }

    /**
     * Get top selling categories
     */
    public function categoryChart()
    {
        $categorySales = DB::table('sale_items')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->selectRaw('categories.name, SUM(sale_items.quantity) as total_quantity, SUM(sale_items.subtotal) as total_sales')
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('total_quantity')
            ->limit(5)
            ->get();

        return response()->json($categorySales);
    }

    /**
     * Get low stock alerts
     */
    public function lowStockAlerts()
    {
        $lowStockProducts = Product::with(['category', 'supplier'])
            ->whereColumn('stock_quantity', '<=', 'minimum_stock')
            ->orderBy('stock_quantity')
            ->limit(10)
            ->get();

        return response()->json($lowStockProducts);
    }

    /**
     * Get recent sales
     */
    public function recentSales()
    {
        $recentSales = Sale::query()
            ->with([
                'user:id,name,role',
                'saleItems:id,sale_id,product_id,quantity',
                'saleItems.product:id,name',
            ])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get()
            ->map(function (Sale $sale) {
                $userName = $sale->user?->name;
                $role = $sale->user?->role;
                $roleLabel = $role === 'kasir' ? 'Kasir' : ($role === 'admin' ? 'Admin' : ($role ? ucfirst($role) : null));

                $servedBy = $userName ? ($roleLabel ? "{$userName} ({$roleLabel})" : $userName) : 'Petugas';
                $ts = $sale->created_at ? $sale->created_at->timezone(config('app.timezone')) : null;

                $items = $sale->saleItems ?? collect();
                $distinctProducts = $items
                    ->pluck('product.name')
                    ->filter(fn ($v) => (string) $v !== '')
                    ->values();
                $firstProduct = $distinctProducts->first();
                $productCount = $distinctProducts->unique()->count();
                $qtyTotal = (int) $items->sum('quantity');

                $activityTitle = 'Penjualan';
                if ($firstProduct) {
                    $activityTitle = "Penjualan: {$firstProduct}";
                    if ($productCount > 1) {
                        $more = $productCount - 1;
                        $activityTitle .= " +{$more} barang";
                    }
                }

                return [
                    'id' => $sale->id,
                    'total_amount' => (float) $sale->total_amount,
                    'served_by' => $servedBy,
                    'time_label' => $ts ? $ts->format('H:i') : '-',
                    'created_at' => $ts ? $ts->toIso8601String() : null,
                    'title' => $activityTitle,
                    'items_count' => $productCount,
                    'qty_total' => $qtyTotal,
                ];
            })
            ->values();

        return response()->json($recentSales);
    }

    public function monthComparison()
    {
        $currentMonthStart = now()->startOfMonth();
        $currentMonthEnd = now()->endOfMonth();
        $lastMonthStart = now()->subMonth()->startOfMonth();
        $lastMonthEnd = now()->subMonth()->endOfMonth();

        $currentMonthSales = Sale::whereBetween('sale_date', [$currentMonthStart, $currentMonthEnd])->sum('total_amount');
        $lastMonthSales = Sale::whereBetween('sale_date', [$lastMonthStart, $lastMonthEnd])->sum('total_amount');

        $diff = $currentMonthSales - $lastMonthSales;
        $percent = $lastMonthSales > 0 ? (($diff / $lastMonthSales) * 100) : ($currentMonthSales > 0 ? 100 : 0);

        return response()->json([
            'current_month' => [
                'label' => now()->format('F Y'),
                'total_sales' => $currentMonthSales,
            ],
            'last_month' => [
                'label' => now()->subMonth()->format('F Y'),
                'total_sales' => $lastMonthSales,
            ],
            'difference' => $diff,
            'percent_change' => round($percent, 1),
            'trend' => $diff >= 0 ? 'up' : 'down',
        ]);
    }

    public function debtManagementSummary()
    {
        $overdueToday = Debt::whereIn('status', ['unpaid', 'partial'])
            ->whereDate('due_date', '<=', now()->toDateString())
            ->count();

        $overdueAmount = Debt::whereIn('status', ['unpaid', 'partial'])
            ->whereDate('due_date', '<=', now()->toDateString())
            ->sum('remaining_amount');

        $debts = Debt::whereIn('status', ['unpaid', 'partial'])
            ->orderBy('due_date')
            ->limit(4)
            ->get()
            ->map(function ($debt) {
                $dueLabel = 'Tanpa jatuh tempo';
                $dueDateFormatted = null;

                if ($debt->due_date) {
                    $dueDateFormatted = $debt->due_date->format('d/m/Y');
                    $today = now()->startOfDay();
                    $due = $debt->due_date->copy()->startOfDay();

                    if ($due->equalTo($today)) {
                        $dueLabel = 'Jatuh tempo hari ini';
                    } elseif ($due->lt($today)) {
                        $dueLabel = 'Terlambat';
                    } else {
                        // Carbon 3: diffInDays default absolute=false → bisa negatif & float; pakai hari kalender.
                        $days = (int) $today->diffInDays($due, true);
                        $dueLabel = $days === 1 ? '1 hari lagi' : $days.' hari lagi';
                    }
                }

                return [
                    'customer_name' => $debt->customer_name,
                    'remaining_amount' => (float) $debt->remaining_amount,
                    'due_label' => $dueLabel,
                    'due_date_formatted' => $dueDateFormatted,
                ];
            });

        return response()->json([
            'overdue_count' => $overdueToday,
            'overdue_amount' => (float) $overdueAmount,
            'items' => $debts,
        ]);
    }

    /**
     * Get sales data by selected period
     */
    public function salesByPeriod(Request $request)
    {
        $period = $request->get('period', 'current_month');
        $groupBy = $request->get('group_by', 'daily');
        $startInput = $request->get('start_date');
        $endInput = $request->get('end_date');

        if ($startInput && $endInput) {
            $startDate = Carbon::parse($startInput)->startOfDay();
            $endDate = Carbon::parse($endInput)->endOfDay();
            $periodLabel = 'Range '.$startDate->format('d M Y').' - '.$endDate->format('d M Y');
        } else {
            switch ($period) {
                case 'today':
                    $startDate = now()->startOfDay();
                    $endDate = now()->endOfDay();
                    $periodLabel = 'Hari Ini';
                    break;
                case 'this_week':
                    $startDate = now()->startOfWeek();
                    $endDate = now()->endOfWeek();
                    $periodLabel = 'Minggu Ini';
                    break;
                case 'this_year':
                    $startDate = now()->startOfYear();
                    $endDate = now()->endOfYear();
                    $periodLabel = 'Tahun Ini';
                    break;
                case '3_months_ago':
                    $startDate = now()->subMonths(3)->startOfMonth();
                    $endDate = now()->subMonths(3)->endOfMonth();
                    $periodLabel = '3 Bulan Lalu ('.now()->subMonths(3)->format('M Y').')';
                    break;
                case '6_months_ago':
                    $startDate = now()->subMonths(6)->startOfMonth();
                    $endDate = now()->subMonths(6)->endOfMonth();
                    $periodLabel = '6 Bulan Lalu ('.now()->subMonths(6)->format('M Y').')';
                    break;
                case 'current_month':
                default:
                    $startDate = now()->startOfMonth();
                    $endDate = now()->endOfMonth();
                    $periodLabel = 'Bulan Ini ('.now()->format('M Y').')';
            }
        }

        $formatMap = [
            'daily' => '%Y-%m-%d',
            'weekly' => '%x-%v',
            'monthly' => '%Y-%m',
            'yearly' => '%Y',
        ];
        $sqlFormat = $formatMap[$groupBy] ?? $formatMap['daily'];

        $groupedSales = Sale::selectRaw("DATE_FORMAT(sale_date, '{$sqlFormat}') as period_key, SUM(total_amount) as total")
            ->whereBetween('sale_date', [$startDate, $endDate])
            ->groupBy('period_key')
            ->orderBy('period_key')
            ->get();

        $chartData = $groupedSales->map(function ($item) use ($groupBy) {
            $label = $item->period_key;
            if ($groupBy === 'daily') {
                $label = Carbon::parse($item->period_key)->format('d M');
            } elseif ($groupBy === 'monthly') {
                $label = Carbon::createFromFormat('Y-m', $item->period_key)->format('M Y');
            } elseif ($groupBy === 'yearly') {
                $label = $item->period_key;
            } elseif ($groupBy === 'weekly') {
                $label = 'Minggu '.substr($item->period_key, -2).' ('.substr($item->period_key, 0, 4).')';
            }

            return [
                'date' => $label,
                'period_key' => $item->period_key,
                'total' => number_format($item->total, 2),
                'raw_total' => (float) $item->total,
            ];
        })->values();

        return response()->json([
            'period' => $periodLabel,
            'group_by' => $groupBy,
            'data' => $chartData,
            'total_sales' => number_format($groupedSales->sum('total'), 2),
            'total_transactions' => Sale::whereBetween('sale_date', [$startDate, $endDate])->count(),
            'range' => [
                'start' => $startDate->toDateString(),
                'end' => $endDate->toDateString(),
            ],
        ]);
    }

    /**
     * Analisis penjualan harian (sumbu 1–31) dengan agregat untuk badge & perbandingan.
     */
    public function salesAnalysis(Request $request)
    {
        $mode = $request->get('mode', 'compare');
        if (! in_array($mode, ['compare', 'single'], true)) {
            $mode = 'compare';
        }

        $monthStr = $request->get('month', now()->format('Y-m'));
        try {
            $primary = Carbon::createFromFormat('Y-m', $monthStr)->startOfMonth();
        } catch (\Exception $e) {
            return response()->json(['error' => 'Format bulan tidak valid (gunakan YYYY-MM).'], 422);
        }

        $primaryStart = $primary->copy();
        $primaryEnd = $primary->copy()->endOfMonth();
        $daysInPrimary = (int) $primary->daysInMonth;

        $prevStart = $primary->copy()->subMonth()->startOfMonth();
        $prevEnd = $primary->copy()->subMonth()->endOfMonth();

        $compareStr = $request->get('compare_month');
        if ($mode === 'compare') {
            if (! $compareStr) {
                $compareStr = $prevStart->format('Y-m');
            }
            try {
                $compareMonth = Carbon::createFromFormat('Y-m', $compareStr)->startOfMonth();
            } catch (\Exception $e) {
                return response()->json(['error' => 'Format bulan pembanding tidak valid.'], 422);
            }
            $compareStart = $compareMonth->copy();
            $compareEnd = $compareMonth->copy()->endOfMonth();
            $daysInCompare = (int) $compareMonth->daysInMonth;
        } else {
            $compareStr = null;
            $compareStart = $compareEnd = null;
            $daysInCompare = 0;
        }

        $buildDaily = function (Carbon $from, Carbon $to, int $maxDayInMonth) {
            $rows = Sale::query()
                ->whereBetween('sale_date', [$from, $to])
                ->selectRaw('DAY(sale_date) as d, COALESCE(SUM(total_amount), 0) as total, COUNT(*) as c')
                ->groupBy('d')
                ->get()
                ->keyBy('d');

            $series = [];
            for ($day = 1; $day <= 31; $day++) {
                if ($day > $maxDayInMonth) {
                    $series[] = null;
                } else {
                    $row = $rows->get($day);
                    $series[] = $row ? [
                        'total' => (float) $row->total,
                        'transactions' => (int) $row->c,
                    ] : [
                        'total' => 0.0,
                        'transactions' => 0,
                    ];
                }
            }

            return $series;
        };

        $primaryDaily = $buildDaily($primaryStart, $primaryEnd, $daysInPrimary);
        $primaryTotal = Sale::whereBetween('sale_date', [$primaryStart, $primaryEnd])->sum('total_amount');
        $primaryTx = Sale::whereBetween('sale_date', [$primaryStart, $primaryEnd])->count();

        $prevTotal = Sale::whereBetween('sale_date', [$prevStart, $prevEnd])->sum('total_amount');
        $prevTx = Sale::whereBetween('sale_date', [$prevStart, $prevEnd])->count();

        if ($mode === 'compare') {
            $compareDaily = $buildDaily($compareStart, $compareEnd, $daysInCompare);
            $compareTotal = Sale::whereBetween('sale_date', [$compareStart, $compareEnd])->sum('total_amount');
        } else {
            $compareDaily = null;
            $compareTotal = null;
        }

        $diffSales = (float) $primaryTotal - (float) $prevTotal;
        if ($prevTotal > 0) {
            $salesPct = round((($primaryTotal - $prevTotal) / $prevTotal) * 100, 1);
        } else {
            $salesPct = $primaryTotal > 0 ? 100.0 : 0.0;
        }
        $txDelta = $primaryTx - $prevTx;
        $avgPerDay = $daysInPrimary > 0 ? round($primaryTotal / $daysInPrimary, 0) : 0;

        $trend = $diffSales > 0 ? 'up' : ($diffSales < 0 ? 'down' : 'neutral');
        $txTrend = $txDelta > 0 ? 'up' : ($txDelta < 0 ? 'down' : 'neutral');

        $label = $primary->copy()->locale('id')->translatedFormat('F Y');
        $prevLabel = $prevStart->copy()->locale('id')->translatedFormat('F Y');
        $compareLabel = null;
        if ($mode === 'compare' && $compareStart) {
            $compareLabel = $compareStart->copy()->locale('id')->translatedFormat('F Y');
        }

        return response()->json([
            'mode' => $mode,
            'month' => $primary->format('Y-m'),
            'month_label' => $label,
            'days_in_month' => $daysInPrimary,
            'primary_daily' => $primaryDaily,
            'summary' => [
                'total_sales' => (float) $primaryTotal,
                'total_sales_fmt' => number_format($primaryTotal, 2, '.', ''),
                'total_transactions' => (int) $primaryTx,
                'avg_per_day' => (float) $avgPerDay,
                'prev_month' => $prevStart->format('Y-m'),
                'prev_month_label' => $prevLabel,
                'prev_total_sales' => (float) $prevTotal,
                'prev_total_transactions' => (int) $prevTx,
                'sales_percent_change' => $salesPct,
                'sales_trend' => $trend,
                'tx_delta' => (int) $txDelta,
                'tx_trend' => $txTrend,
            ],
            'compare' => $mode === 'compare' && $compareStr ? [
                'month' => $compareStr,
                'label' => $compareLabel,
                'total_sales' => (float) $compareTotal,
                'total_sales_fmt' => number_format($compareTotal, 2, '.', ''),
                'days_in_month' => $daysInCompare,
                'daily' => $compareDaily,
            ] : null,
        ]);
    }

    public function topProducts(Request $request)
    {
        $start = $request->get('start_date');
        $end = $request->get('end_date');
        $period = $request->get('period', 'current_month');
        $ym = $request->get('month');
        $limit = (int) $request->get('limit', 5);

        if ($ym) {
            try {
                $m = Carbon::createFromFormat('Y-m', $ym)->startOfMonth();
                $startDate = $m->copy();
                $endDate = $m->copy()->endOfMonth();
            } catch (\Exception $e) {
                $startDate = now()->startOfMonth();
                $endDate = now()->endOfMonth();
            }
        } elseif ($start && $end) {
            $startDate = Carbon::parse($start)->startOfDay();
            $endDate = Carbon::parse($end)->endOfDay();
        } else {
            switch ($period) {
                case 'today':
                    $startDate = now()->startOfDay();
                    $endDate = now()->endOfDay();
                    break;
                case 'this_week':
                    $startDate = now()->startOfWeek();
                    $endDate = now()->endOfWeek();
                    break;
                case 'this_year':
                    $startDate = now()->startOfYear();
                    $endDate = now()->endOfYear();
                    break;
                case 'last_3_months':
                    $startDate = now()->subMonths(2)->startOfMonth();
                    $endDate = now()->endOfMonth();
                    break;
                case 'all_time':
                    $startDate = Carbon::create(2000, 1, 1)->startOfDay();
                    $endDate = now()->endOfDay();
                    break;
                case 'current_month':
                default:
                    $startDate = now()->startOfMonth();
                    $endDate = now()->endOfMonth();
            }
        }

        $topProducts = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->whereBetween('sales.sale_date', [$startDate, $endDate])
            ->selectRaw('products.name, SUM(sale_items.quantity) as total_qty, SUM(sale_items.subtotal) as total_sales')
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_qty')
            ->limit($limit)
            ->get();

        return response()->json($topProducts);
    }
}
