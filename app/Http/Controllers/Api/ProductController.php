<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Product::with(['category', 'supplier']);

        // Filter — beberapa kategori (kasir: tab Pupuk / alat / perlindungan tanaman)
        if ($request->filled('category_ids')) {
            $raw = trim((string) $request->category_ids);
            if ($raw === '__none__') {
                $query->whereRaw('0 = 1');
            } else {
                $parts = explode(',', $raw);
                $ids = array_values(array_unique(array_filter(array_map('intval', $parts))));
                $ids = array_slice($ids, 0, 40);
                if (count($ids) > 0) {
                    $query->whereIn('category_id', $ids);
                }
            }
        } elseif ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }
        if ($request->filled('stock_status')) {
            if ($request->stock_status === 'low') {
                $query->whereColumn('stock_quantity', '<=', 'minimum_stock');
            } elseif ($request->stock_status === 'empty') {
                $query->where('stock_quantity', 0);
            } elseif ($request->stock_status === 'available') {
                $query->where('stock_quantity', '>', 0);
            }
        }
        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                    ->orWhere('brand', 'like', "%$search%")
                    ->orWhere('barcode', 'like', "%$search%")
                    ->orWhere('description', 'like', "%$search%")
                    ->orWhereHas('category', function ($cq) use ($search) {
                        $cq->where('name', 'like', "%$search%");
                    });
            });
        }
        $perPage = min(max((int) $request->get('per_page', 15), 1), 500);
        $products = $query->orderBy('name')->paginate($perPage);
        return ProductResource::collection($products);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request)
    {
        $data = $request->validated();
        DB::beginTransaction();

        try {
            // Handle image upload
            if ($request->hasFile('image')) {
                $data['image'] = $request->file('image')->store('products', 'public');
            }
            $product = Product::create($data);

            if ((int) $product->stock_quantity > 0) {
                StockMovement::create([
                    'product_id' => $product->id,
                    'type' => 'in',
                    'quantity' => (int) $product->stock_quantity,
                    'reference_type' => 'product_create',
                    'reference_id' => $product->id,
                    'notes' => 'Stok awal saat produk dibuat',
                    'user_id' => Auth::id() ?? 1,
                ]);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        $product->load(['category', 'supplier']);
        return new ProductResource($product);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $product = Product::with(['category', 'supplier'])->findOrFail($id);
        return new ProductResource($product);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreProductRequest $request, $id)
    {
        $product = Product::findOrFail($id);
        $data = $request->validated();

        DB::beginTransaction();

        try {
            $oldStock = (int) $product->stock_quantity;

            // Handle image upload
            if ($request->hasFile('image')) {
                // Delete old image if exists
                if ($product->image) {
                    Storage::disk('public')->delete($product->image);
                }
                $data['image'] = $request->file('image')->store('products', 'public');
            }

            $product->update($data);

            $newStock = (int) $product->stock_quantity;
            $delta = $newStock - $oldStock;

            if ($delta !== 0) {
                StockMovement::create([
                    'product_id' => $product->id,
                    'type' => $delta > 0 ? 'in' : 'out',
                    'quantity' => abs($delta),
                    'reference_type' => 'product_edit',
                    'reference_id' => $product->id,
                    'notes' => "Penyesuaian stok dari edit produk ({$oldStock} -> {$newStock})",
                    'user_id' => Auth::id() ?? 1,
                ]);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        $product->load(['category', 'supplier']);
        return new ProductResource($product);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete(); // Soft delete
        return response()->json(['success' => true]);
    }
}
