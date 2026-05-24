<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Supplier;
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
        $filteredQuery = $this->filteredProductsQuery($request);

        $products = (clone $filteredQuery)
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        $stats = $this->productListStats($request);

        $categories = Category::ordered()->get();
        $suppliers = Supplier::all();

        return view('products.index', compact('products', 'categories', 'suppliers', 'stats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::ordered()->get();
        $suppliers = Supplier::all();
        return view('products.create', compact('categories', 'suppliers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->merge([
            'price' => $this->normalizePrice($request->input('price')),
            'supplier_id' => $request->filled('supplier_id') ? $request->input('supplier_id') : null,
        ]);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'brand' => 'required|string|max:255',
            'type' => 'required|in:organik,kimia',
            'unit' => 'required|in:kg,liter,karung',
            'price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'minimum_stock' => 'required|integer|min:0',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'barcode' => 'nullable|string|max:255|unique:products,barcode',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'category_id' => 'required|exists:categories,id',
        ]);
        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products', 'public');
        }
        DB::beginTransaction();

        try {
            $product = Product::create($validated);

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

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Produk berhasil ditambahkan!',
                'data' => $this->productPayload($product->fresh()),
                'stats' => $this->productListStats($request),
            ]);
        }

        return $this->productsIndexRedirect($request, 'Produk berhasil ditambahkan!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        $categories = Category::ordered()->get();
        $suppliers = Supplier::all();
        return view('products.edit', compact('product', 'categories', 'suppliers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $request->merge([
            'price' => $this->normalizePrice($request->input('price')),
            'supplier_id' => $request->filled('supplier_id') ? $request->input('supplier_id') : null,
        ]);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'brand' => 'required|string|max:255',
            'type' => 'required|in:organik,kimia',
            'unit' => 'required|in:kg,liter,karung',
            'price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'minimum_stock' => 'required|integer|min:0',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'barcode' => 'nullable|string|max:255|unique:products,barcode,' . $product->id,
            'supplier_id' => 'nullable|exists:suppliers,id',
            'category_id' => 'required|exists:categories,id',
        ]);
        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $validated['image'] = $request->file('image')->store('products', 'public');
        } elseif ($request->has('remove_image') && $request->remove_image == '1') {
            // Remove existing image if remove_image flag is set
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
                $validated['image'] = null;
            }
        }
        DB::beginTransaction();

        try {
            $oldStock = (int) $product->stock_quantity;

            $product->update($validated);

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

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Produk berhasil diupdate!',
                'data' => $this->productPayload($product->fresh()),
                'stats' => $this->productListStats($request),
            ]);
        }

        return $this->productsIndexRedirect($request, 'Produk berhasil diupdate!');
    }

    private function normalizePrice($value): float
    {
        if (is_numeric($value)) {
            return (float) $value;
        }

        $raw = preg_replace('/[^\d,\.]/', '', (string) $value);

        if ($raw === '') {
            return 0;
        }

        if (str_contains($raw, '.') && str_contains($raw, ',')) {
            $raw = str_replace('.', '', $raw);
            $raw = str_replace(',', '.', $raw);
            return (float) $raw;
        }

        if (str_contains($raw, ',')) {
            // Common Indonesian input: 679,000 -> 679000
            return (float) str_replace(',', '', $raw);
        }

        if (str_contains($raw, '.')) {
            $segments = explode('.', $raw);
            if (count($segments) > 2 || strlen(end($segments)) === 3) {
                // Treat as thousand separators: 679.000
                return (float) str_replace('.', '', $raw);
            }
        }

        return (float) $raw;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Product $product)
    {
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }
        $product->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Produk berhasil dihapus!',
                'id' => $product->id,
                'stats' => $this->productListStats($request),
            ]);
        }

        return $this->productsIndexRedirect($request, 'Produk berhasil dihapus!');
    }

    private function filteredProductsQuery(Request $request)
    {
        $query = Product::with(['category', 'supplier']);

        if ($request->filled('q')) {
            $term = trim((string) $request->q);
            $query->where(function ($builder) use ($term) {
                $builder->where('name', 'like', '%' . $term . '%')
                    ->orWhere('barcode', 'like', '%' . $term . '%');
            });
        }

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        if ($request->status === 'safe') {
            $query->whereColumn('stock_quantity', '>=', 'minimum_stock');
        } elseif ($request->status === 'low') {
            $query->whereColumn('stock_quantity', '<', 'minimum_stock');
        }

        return $query;
    }

    /**
     * @return array{total_products:int,total_stock:int,safe_stock_count:int,restock_count:int}
     */
    private function productListStats(Request $request): array
    {
        $query = $this->filteredProductsQuery($request);

        return [
            'total_products' => (clone $query)->count(),
            'total_stock' => (int) (clone $query)->sum('stock_quantity'),
            'safe_stock_count' => (clone $query)->whereColumn('stock_quantity', '>=', 'minimum_stock')->count(),
            'restock_count' => (clone $query)->whereColumn('stock_quantity', '<', 'minimum_stock')->count(),
        ];
    }

    private function productsIndexRedirect(Request $request, string $message)
    {
        $params = array_filter(
            $request->only(['q', 'category', 'status', 'page']),
            fn ($value) => $value !== null && $value !== ''
        );

        return redirect()->route('products.index', $params)->with('success', $message);
    }

    private function productPayload(Product $product): array
    {
        $product->loadMissing(['category', 'supplier']);

        $categoryName = strtolower($product->category->name ?? '');
        $categoryClass = 'category-pill-pupuk';
        if (str_contains($categoryName, 'herbisida')) {
            $categoryClass = 'category-pill-herbisida';
        } elseif (str_contains($categoryName, 'pestisida')) {
            $categoryClass = 'category-pill-pestisida';
        } elseif (str_contains($categoryName, 'alat')) {
            $categoryClass = 'category-pill-alat';
        }

        $isLow = $product->stock_quantity < $product->minimum_stock;
        $progress = $product->minimum_stock > 0
            ? min(($product->stock_quantity / ($product->minimum_stock * 10)) * 100, 100)
            : 100;

        return [
            'id' => $product->id,
            'barcode' => $product->barcode ?? 'BRG-' . str_pad((string) $product->id, 3, '0', STR_PAD_LEFT),
            'name' => $product->name,
            'category_name' => $product->category->name ?? '-',
            'category_class' => $categoryClass,
            'unit' => strtoupper($product->unit),
            'price_formatted' => number_format((int) $product->price, 0, ',', '.'),
            'stock_quantity' => (int) $product->stock_quantity,
            'stock_quantity_formatted' => number_format((int) $product->stock_quantity, 0, ',', '.'),
            'minimum_stock' => (int) $product->minimum_stock,
            'minimum_stock_formatted' => number_format((int) $product->minimum_stock, 0, ',', '.'),
            'is_low' => $isLow,
            'progress' => $progress,
        ];
    }
}
