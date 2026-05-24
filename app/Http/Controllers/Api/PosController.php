<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSaleRequest;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockMovement;
use App\Models\Debt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PosController extends Controller
{
    /**
     * Search products for POS
     */
    public function searchProducts(Request $request)
    {
        $query = $request->get('q');

        if (!$query) {
            return response()->json(['data' => []]);
        }

        $products = Product::with(['category', 'supplier'])
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%$query%")
                    ->orWhere('brand', 'like', "%$query%")
                    ->orWhere('barcode', 'like', "%$query%");
            })
            ->where('stock_quantity', '>', 0) // Hanya produk yang ada stok
            ->limit(10)
            ->get();

        return response()->json(['data' => $products]);
    }

    /**
     * Get product by barcode
     */
    public function getProductByBarcode(Request $request)
    {
        $barcode = $request->get('barcode');

        $product = Product::with(['category', 'supplier'])
            ->where('barcode', $barcode)
            ->where('stock_quantity', '>', 0)
            ->first();

        if (!$product) {
            return response()->json(['message' => 'Product not found or out of stock'], 404);
        }

        return response()->json(['data' => $product]);
    }

    /**
     * Create new sale transaction
     */
    public function createSale(StoreSaleRequest $request)
    {
        DB::beginTransaction();

        try {
            $data = $request->validated();

            // Recompute total from items on server-side to avoid client manipulation
            $calculatedTotal = 0;
            foreach ($data['items'] as $item) {
                // ensure keys exist
                $qty = isset($item['quantity']) ? (int) $item['quantity'] : 0;
                $unit = isset($item['unit_price']) ? (float) $item['unit_price'] : 0;
                $calculatedTotal += $qty * $unit;
            }

            // discount adalah nominal (Rp), bukan persen
            $discount = isset($data['discount']) ? (float) $data['discount'] : 0;
            $shippingFee = isset($data['shipping_fee']) ? (float) $data['shipping_fee'] : 0;
            $discountAmount = max(0, min($calculatedTotal, $discount));
            $afterDisc = max(0, $calculatedTotal - $discountAmount) + $shippingFee;

            $payment = isset($data['payment']) ? (float) $data['payment'] : 0;

            // If payment is less than after-disc and payment_method is not 'credit', reject
            $paymentMethod = $data['payment_method'] ?? 'cash';
            if ($payment < $afterDisc && $paymentMethod !== 'credit') {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Pembayaran tidak mencukupi. Gunakan payment_method=credit untuk menyimpan sebagai piutang.'
                ], 422);
            }

            $change = $payment - $afterDisc;
            if ($change < 0) {
                $change = 0; // store non-negative change
            }

            // Calculate debt amount
            $debtAmount = $afterDisc - $payment;
            if ($debtAmount < 0) {
                $debtAmount = 0;
            }

            // Determine debt status
            $debtStatus = 'paid';
            if ($debtAmount > 0) {
                $debtStatus = $payment > 0 ? 'partial' : 'unpaid';
            }

            // set status: pending when credit, completed otherwise
            $status = $paymentMethod === 'credit' ? 'pending' : 'completed';

            // Get delivery method and address
            $deliveryMethod = $data['delivery_method'] ?? 'pickup';
            $deliveryAddress = $data['delivery_address'] ?? null;
            $deliveryPhone = $data['delivery_phone'] ?? null;

            // Create sale
            $sale = Sale::create([
                'invoice_number' => Sale::generateInvoiceNumber(),
                'customer_name' => $data['customer_name'] ?? null,
                'customer_phone' => $data['customer_phone'] ?? null,
                'delivery_method' => $deliveryMethod,
                'delivery_address' => $deliveryAddress,
                'delivery_phone' => $deliveryPhone,
                'sale_date' => now()->format('Y-m-d'),
                'discount' => $discountAmount,
                'total_amount' => $calculatedTotal + $shippingFee,
                'payment_method' => $paymentMethod,
                'payment_amount' => $payment,
                'change_amount' => $change,
                'debt_amount' => $debtAmount,
                'debt_status' => $debtStatus,
                'status' => $status,
                'user_id' => Auth::id() ?? 1,
            ]);

            // Create sale items and update stock
            foreach ($data['items'] as $item) {
                $product = Product::find($item['product_id']);

                if (!$product) {
                    throw new \Exception("Product not found: " . $item['product_id']);
                }

                if ($product->stock_quantity < $item['quantity']) {
                    throw new \Exception("Insufficient stock for product: " . $product->name);
                }

                $qty = (int) $item['quantity'];
                $unit = (float) $item['unit_price'];
                $subtotal = $qty * $unit;

                // Create sale item
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'unit_price' => $unit,
                    'subtotal' => $subtotal,
                ]);

                // Update product stock
                $product->decrement('stock_quantity', $qty);

                // Create stock movement record
                StockMovement::create([
                    'product_id' => $product->id,
                    'type' => 'out',
                    'quantity' => $qty,
                    'reference_type' => 'sale',
                    'reference_id' => $sale->id,
                    'notes' => 'Sale transaction: ' . $sale->invoice_number,
                    'user_id' => Auth::id() ?? 1,
                ]);
            }

            // Create debt record if there's debt
            if ($debtAmount > 0) {
                $existingNotes = $data['notes'] ?? null;
                $shippingNote = $shippingFee > 0 ? 'Termasuk ongkir: Rp ' . number_format($shippingFee, 0, ',', '.') : null;
                $mergedNotes = trim(implode(' | ', array_filter([$existingNotes, $shippingNote])));
                Debt::create([
                    'sale_id' => $sale->id,
                    'customer_name' => $data['customer_name'] ?? 'Pelanggan',
                    'customer_phone' => $data['customer_phone'] ?? null,
                    'total_amount' => $afterDisc,
                    'paid_amount' => $payment,
                    'remaining_amount' => $debtAmount,
                    'due_date' => $data['due_date'] ?? now()->addDays(30)->format('Y-m-d'),
                    'status' => $debtStatus,
                    'notes' => $mergedNotes !== '' ? $mergedNotes : null,
                    'user_id' => Auth::id() ?? 1,
                ]);
            }

            DB::commit();

            // Load sale with items and products
            $sale->load(['saleItems.product', 'user']);

            return response()->json([
                'success' => true,
                'message' => 'Sale transaction completed successfully',
                'data' => $sale
            ], 201);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Transaction failed: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get sale history
     */
    public function getSaleHistory(Request $request)
    {
        $query = Sale::with(['saleItems.product', 'user']);

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->whereDate('sale_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('sale_date', '<=', $request->end_date);
        }

        // Filter by payment method
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        $sales = $query->orderByDesc('sale_date')
            ->paginate(15);

        return response()->json($sales);
    }

    /**
     * Get sale detail
     */
    public function getSaleDetail($id)
    {
        $sale = Sale::with(['saleItems.product.category', 'user'])
            ->findOrFail($id);

        return response()->json(['data' => $sale]);
    }

    /**
     * Cancel sale (soft delete)
     */
    public function cancelSale($id)
    {
        DB::beginTransaction();

        try {
            $sale = Sale::findOrFail($id);

            // Check if sale can be cancelled (within 24 hours)
            if (now()->diffInHours($sale->created_at) > 24) {
                throw new \Exception('Sale can only be cancelled within 24 hours');
            }

            // Restore stock for each item
            foreach ($sale->saleItems as $item) {
                $product = $item->product;
                $product->increment('stock_quantity', $item->quantity);

                // Create stock movement record for cancellation
                StockMovement::create([
                    'product_id' => $product->id,
                    'type' => 'in',
                    'quantity' => $item->quantity,
                    'reference_type' => 'sale_cancellation',
                    'reference_id' => $sale->id,
                    'notes' => 'Sale cancellation: ' . $sale->invoice_number,
                    'user_id' => Auth::id() ?? 1,
                ]);
            }

            // Soft delete sale
            $sale->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Sale cancelled successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Cancellation failed: ' . $e->getMessage()
            ], 400);
        }
    }

    public function store(Request $request)
    {
        // Validasi data
        $validated = $request->validate([
            'customer' => 'nullable|string|max:255',
            'discount' => 'nullable|numeric|min:0|max:100',
            'payment' => 'required|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|integer|min:1',
        ]);

        // Simpan data penjualan (contoh sederhana)
        // ... proses simpan ke tabel sales dan sale_items

        return response()->json(['message' => 'Transaksi berhasil disimpan!']);
    }
}
