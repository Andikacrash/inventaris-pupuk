<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StockAdjustmentRequest;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class StockController extends Controller
{
    /**
     * Get stock history for a product
     */
    public function getStockHistory(Request $request)
    {
        $productId = $request->get('product_id');
        $type = $request->get('type'); // in, out, all
        $limit = $request->get('limit', 50);

        $query = StockMovement::with(['product', 'user']);

        if ($productId) {
            $query->where('product_id', $productId);
        }

        if ($type && $type !== 'all') {
            $query->where('type', $type);
        }

        $movements = $query->orderByDesc('created_at')
            ->paginate($limit);

        return response()->json($movements);
    }

    /**
     * Adjust stock manually
     */
    public function adjustStock(StockAdjustmentRequest $request)
    {
        DB::beginTransaction();

        try {
            $product = Product::findOrFail($request->product_id);
            $adjustmentType = $request->adjustment_type; // increase, decrease, set
            $quantity = $request->quantity;
            $notes = $request->notes;

            $oldStock = $product->stock_quantity;

            switch ($adjustmentType) {
                case 'increase':
                    $newStock = $oldStock + $quantity;
                    $movementType = 'in';
                    break;
                case 'decrease':
                    if ($oldStock < $quantity) {
                        throw new \Exception('Insufficient stock for decrease');
                    }
                    $newStock = $oldStock - $quantity;
                    $movementType = 'out';
                    break;
                case 'set':
                    $newStock = $quantity;
                    $movementType = $quantity > $oldStock ? 'in' : 'out';
                    $quantity = abs($quantity - $oldStock);
                    break;
                default:
                    throw new \Exception('Invalid adjustment type');
            }

            // Update product stock
            $product->update(['stock_quantity' => $newStock]);

            // Create stock movement record
            StockMovement::create([
                'product_id' => $product->id,
                'type' => $movementType,
                'quantity' => $quantity,
                'reference_type' => 'adjustment',
                'reference_id' => 0, // No specific reference for manual adjustment
                'notes' => $notes ?: "Manual stock adjustment: $adjustmentType $quantity units",
                'user_id' => Auth::id() ?? 1,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Stock adjusted successfully',
                'data' => [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'old_stock' => $oldStock,
                    'new_stock' => $newStock,
                    'adjustment' => $adjustmentType,
                    'quantity' => $quantity,
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Stock adjustment failed: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get low stock alerts
     */
    public function getLowStockAlerts()
    {
        $lowStockProducts = Product::with(['category', 'supplier'])
            ->whereColumn('stock_quantity', '<=', 'minimum_stock')
            ->orderBy('stock_quantity')
            ->get();

        return response()->json([
            'count' => $lowStockProducts->count(),
            'data' => $lowStockProducts
        ]);
    }

    /**
     * Get stock prediction (simple analytics)
     */
    public function getStockPrediction(Request $request)
    {
        $productId = $request->get('product_id');
        $days = $request->get('days', 30);

        if ($productId) {
            $product = Product::findOrFail($productId);

            // Get sales data for the last X days
            $salesData = DB::table('sale_items')
                ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
                ->where('sale_items.product_id', $productId)
                ->where('sales.sale_date', '>=', now()->subDays($days))
                ->selectRaw('
                    DATE(sales.sale_date) as date,
                    SUM(sale_items.quantity) as daily_sales
                ')
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            // Calculate average daily sales
            $totalSales = $salesData->sum('daily_sales');
            $averageDailySales = $totalSales > 0 ? $totalSales / $days : 0;

            // Calculate days until stock out
            $daysUntilStockOut = $averageDailySales > 0 ? floor($product->stock_quantity / $averageDailySales) : null;

            // Calculate recommended restock quantity (30 days supply)
            $recommendedRestock = $averageDailySales * 30;

            return response()->json([
                'product' => $product,
                'analysis_period' => $days . ' days',
                'total_sales_period' => $totalSales,
                'average_daily_sales' => round($averageDailySales, 2),
                'current_stock' => $product->stock_quantity,
                'days_until_stockout' => $daysUntilStockOut,
                'recommended_restock' => round($recommendedRestock),
                'sales_data' => $salesData
            ]);
        }

        // Get prediction for all products
        $products = Product::where('stock_quantity', '>', 0)->get();
        $predictions = [];

        foreach ($products as $product) {
            $salesData = DB::table('sale_items')
                ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
                ->where('sale_items.product_id', $product->id)
                ->where('sales.sale_date', '>=', now()->subDays($days))
                ->sum('sale_items.quantity');

            $averageDailySales = $salesData > 0 ? $salesData / $days : 0;
            $daysUntilStockOut = $averageDailySales > 0 ? floor($product->stock_quantity / $averageDailySales) : null;

            $predictions[] = [
                'product' => $product,
                'average_daily_sales' => round($averageDailySales, 2),
                'days_until_stockout' => $daysUntilStockOut,
                'needs_restock' => $daysUntilStockOut !== null && $daysUntilStockOut <= 7
            ];
        }

        return response()->json([
            'analysis_period' => $days . ' days',
            'data' => $predictions
        ]);
    }

    /**
     * Bulk stock adjustment
     */
    public function bulkStockAdjustment(Request $request)
    {
        $request->validate([
            'adjustments' => 'required|array|min:1',
            'adjustments.*.product_id' => 'required|exists:products,id',
            'adjustments.*.adjustment_type' => 'required|in:increase,decrease,set',
            'adjustments.*.quantity' => 'required|integer|min:1',
            'adjustments.*.notes' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            $results = [];

            foreach ($request->adjustments as $adjustment) {
                $product = Product::find($adjustment['product_id']);
                $adjustmentType = $adjustment['adjustment_type'];
                $quantity = $adjustment['quantity'];
                $notes = $adjustment['notes'] ?? '';

                $oldStock = $product->stock_quantity;

                switch ($adjustmentType) {
                    case 'increase':
                        $newStock = $oldStock + $quantity;
                        $movementType = 'in';
                        break;
                    case 'decrease':
                        if ($oldStock < $quantity) {
                            throw new \Exception("Insufficient stock for product: {$product->name}");
                        }
                        $newStock = $oldStock - $quantity;
                        $movementType = 'out';
                        break;
                    case 'set':
                        $newStock = $quantity;
                        $movementType = $quantity > $oldStock ? 'in' : 'out';
                        $quantity = abs($quantity - $oldStock);
                        break;
                }

                // Update product stock
                $product->update(['stock_quantity' => $newStock]);

                // Create stock movement record
                StockMovement::create([
                    'product_id' => $product->id,
                    'type' => $movementType,
                    'quantity' => $quantity,
                    'reference_type' => 'bulk_adjustment',
                    'reference_id' => 0,
                    'notes' => $notes ?: "Bulk stock adjustment: $adjustmentType $quantity units",
                    'user_id' => Auth::id() ?? 1,
                ]);

                $results[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'old_stock' => $oldStock,
                    'new_stock' => $newStock,
                    'adjustment' => $adjustmentType,
                    'quantity' => $quantity,
                    'status' => 'success'
                ];
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Bulk stock adjustment completed successfully',
                'data' => $results
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Bulk stock adjustment failed: ' . $e->getMessage()
            ], 400);
        }
    }
}
