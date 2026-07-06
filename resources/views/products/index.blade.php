@extends('layouts.app')

@section('content')
    @php
        $totalProducts = $stats['total_products'] ?? $products->total();
        $totalStock = $stats['total_stock'] ?? 0;
        $safeStockCount = $stats['safe_stock_count'] ?? 0;
        $restockCount = $stats['restock_count'] ?? 0;
    @endphp
    <div class="product-modern-page">
        <div class="product-modern-header">
            <div>
                <h2 class="product-modern-title">Daftar Barang</h2>
                <p class="product-modern-subtitle">Kelola data barang dan stok toko pupuk secara rapi</p>
            </div>
            <div class="product-modern-actions">
                <button type="button" class="btn btn-modern-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                    </svg>
                    Tambah Barang
                </button>
            </div>
        </div>

        <div id="product-toast" class="product-toast" role="status" aria-live="polite" hidden></div>

        @if (session('success'))
            <div class="alert alert-success mb-3" id="product-flash-success">
                {{ session('success') }}
            </div>
        @endif

        <div class="product-stats-grid" id="product-stats-grid">
            <div class="product-stat-card" data-stat="total_products">
                <div class="stat-label">Total Barang</div>
                <div class="stat-value" data-stat-value>{{ number_format($totalProducts, 0, ',', '.') }}</div>
                <span class="stat-badge success">Semua aktif</span>
            </div>
            <div class="product-stat-card" data-stat="total_stock">
                <div class="stat-label">Total Stok</div>
                <div class="stat-value" data-stat-value>{{ number_format($totalStock, 0, ',', '.') }}</div>
                <span class="stat-badge success">Stok tercatat</span>
            </div>
            <div class="product-stat-card" data-stat="safe_stock_count">
                <div class="stat-label">Stok Aman</div>
                <div class="stat-value" data-stat-value>{{ number_format($safeStockCount, 0, ',', '.') }}</div>
                <span class="stat-badge success">Di atas minimum</span>
            </div>
            <div class="product-stat-card" data-stat="restock_count">
                <div class="stat-label">Perlu Restock</div>
                <div class="stat-value" data-stat-value>{{ number_format($restockCount, 0, ',', '.') }}</div>
                <span class="stat-badge warning">Perlu tindakan</span>
            </div>
        </div>

        <div class="product-toolbar mb-3">
            <form class="product-toolbar-form" method="GET">
                <div class="product-search-wrap">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="m21 21-4.35-4.35M11 18a7 7 0 1 1 0-14 7 7 0 0 1 0 14Z" stroke="currentColor"
                            stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <input type="text" name="q" class="form-control" value="{{ request('q') }}"
                        placeholder="Cari nama atau kode barang...">
                </div>
                <div class="toolbar-select">
                    <select name="category" class="form-select">
                        <option value="">Semua kategori</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" {{ (string) request('category') === (string) $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="toolbar-select">
                    <select name="status" class="form-select">
                        <option value="">Semua status</option>
                        <option value="safe" {{ request('status') === 'safe' ? 'selected' : '' }}>Stok aman</option>
                        <option value="low" {{ request('status') === 'low' ? 'selected' : '' }}>Perlu restock</option>
                    </select>
                </div>
            </form>
        </div>

        <div class="table-responsive product-modern-table-wrap">
            <table class="table align-middle product-modern-table mb-0">
                <thead>
                    <tr>
                        <th>Kode barang</th>
                        <th>Nama barang</th>
                        <th>Kategori</th>
                        <th>Satuan</th>
                        <th>Harga jual</th>
                        <th>Stok</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody id="product-table-body">
                    @forelse ($products as $product)
                        @php
                            $isLow = $product->stock_quantity < $product->minimum_stock;
                            $progress = $product->minimum_stock > 0 ? min(($product->stock_quantity / ($product->minimum_stock * 10)) * 100, 100) : 100;
                            $categoryName = strtolower($product->category->name ?? '');
                            $categoryClass = 'category-pill-pupuk';
                            if (str_contains($categoryName, 'herbisida')) {
                                $categoryClass = 'category-pill-herbisida';
                            } elseif (str_contains($categoryName, 'pestisida')) {
                                $categoryClass = 'category-pill-pestisida';
                            } elseif (str_contains($categoryName, 'alat')) {
                                $categoryClass = 'category-pill-alat';
                            }
                        @endphp
                        <tr data-product-id="{{ $product->id }}">
                            <td class="text-nowrap">
                                <span class="code-chip">{{ $product->barcode ?? 'BRG-' . str_pad($product->id, 3, '0', STR_PAD_LEFT) }}</span>
                            </td>
                            <td class="product-name-cell">{{ $product->name }}</td>
                            <td class="category-col"><span class="category-pill {{ $categoryClass }}">{{ $product->category->name ?? '-' }}</span></td>
                            <td class="text-muted">{{ strtoupper($product->unit) }}</td>
                            <td class="price-cell">Rp {{ number_format((int) $product->price, 0, ',', '.') }}</td>
                            <td>
                                <div class="stock-cell">
                                    <div class="stock-top">
                                        <span>{{ number_format($product->stock_quantity, 0, ',', '.') }}</span>
                                        <small>min {{ number_format($product->minimum_stock, 0, ',', '.') }}</small>
                                    </div>
                                    <div class="stock-progress-track">
                                        <div class="stock-progress-fill {{ $isLow ? 'low' : 'safe' }}"
                                            style="width: {{ $progress }}%"></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="status-active">
                                    <span class="status-dot"></span>
                                    Aktif
                                </span>
                            </td>
                            <td class="text-end">
                                <button type="button" class="action-icon-btn action-edit" title="Edit"
                                    data-bs-toggle="modal" data-bs-target="#editProductModal-{{ $product->id }}">
                                    <svg class="action-icon-svg" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="m4 20 4.5-1 9-9a2.1 2.1 0 1 0-3-3l-9 9L4 20Z" stroke="currentColor"
                                            stroke-width="2" stroke-linejoin="round" />
                                    </svg>
                                </button>
                                <form action="{{ route('products.destroy', $product) }}" method="POST"
                                    class="d-inline js-product-delete-form" data-product-id="{{ $product->id }}">
                                    @csrf
                                    @method('DELETE')
                                    <button class="action-icon-btn action-delete" type="submit" title="Hapus">
                                        <svg class="action-icon-svg" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                            <path d="M4 7h16M9 7V4h6v3m-7 3v8m4-8v8m4-8v8M6 7l1 13h10l1-13" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" />
                                        </svg>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">Tidak ada barang sesuai filter</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="product-table-footer">
                <span id="product-table-count">Menampilkan {{ $products->count() }} dari {{ number_format($totalProducts, 0, ',', '.') }} barang</span>
                <span>Terakhir diperbarui: {{ now()->format('d M Y, H:i') }}</span>
            </div>
        </div>
        <div class="product-pagination">
            {{ $products->links() }}
        </div>
    </div>

    <!-- Modal Tambah Barang -->
    <div class="modal fade product-form-modal" id="addProductModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content product-form-content">
                <div class="modal-header product-form-header">
                    <h5 class="modal-title mb-0">Tambah Barang Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body product-form-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <strong>Terdapat kesalahan:</strong>
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data"
                        id="add-product-form" class="product-form-grid js-product-ajax-form" data-form-action="create">
                        @csrf
                        <div class="product-field product-field-full">
                            <label class="product-field-label">Nama barang <span class="req">*</span></label>
                            <input type="text" name="name" class="form-control product-input" value="{{ old('name') }}" required
                                placeholder="Contoh: Pupuk NPK 25kg">
                        </div>

                        <div class="product-field">
                            <label class="product-field-label">Kode barang</label>
                            <input type="text" name="barcode" class="form-control product-input" value="{{ old('barcode') }}"
                                placeholder="Contoh: BRG-001">
                        </div>

                        <div class="product-field">
                            <label class="product-field-label">Kategori <span class="req">*</span></label>
                            <select name="category_id" class="form-select product-input" required>
                                <option value="">Pilih kategori</option>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                        {{ $cat->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="product-field">
                            <label class="product-field-label">Supplier <span class="text-muted fw-normal">(opsional)</span></label>
                            <select name="supplier_id" class="form-select product-input">
                                <option value="">— Belum ditentukan —</option>
                                @foreach ($suppliers as $sup)
                                    <option value="{{ $sup->id }}" {{ old('supplier_id') == $sup->id ? 'selected' : '' }}>
                                        {{ $sup->name }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted d-block mt-1">Supplier baru? <a href="{{ route('suppliers.index') }}" target="_blank" rel="noopener">Kelola supplier</a>.</small>
                        </div>

                        <div class="product-field">
                            <label class="product-field-label">Satuan <span class="req">*</span></label>
                            <select name="unit" class="form-select product-input" required>
                                <option value="kg" {{ old('unit', 'kg') === 'kg' ? 'selected' : '' }}>Kg</option>
                                <option value="liter" {{ old('unit') == 'liter' ? 'selected' : '' }}>Liter</option>
                                <option value="karung" {{ old('unit') == 'karung' ? 'selected' : '' }}>Karung</option>
                            </select>
                        </div>

                        <div class="product-field">
                            <label class="product-field-label">Brand <span class="req">*</span></label>
                            <input type="text" name="brand" class="form-control product-input" value="{{ old('brand') }}" required>
                        </div>

                        <div class="product-field">
                            <label class="product-field-label">Jenis <span class="req">*</span></label>
                            <select name="type" class="form-select product-input" required>
                                <option value="organik" {{ old('type', 'organik') === 'organik' ? 'selected' : '' }}>Organik</option>
                                <option value="kimia" {{ old('type') === 'kimia' ? 'selected' : '' }}>Kimia</option>
                            </select>
                        </div>

                        <div class="product-section-label">Stok & Harga</div>
                        <div class="product-triple-grid">
                            <div class="product-field">
                                <label class="product-field-label">Harga jual <span class="req">*</span></label>
                                <div class="price-input-wrap">
                                    <span class="price-prefix">Rp</span>
                                    <input type="text" class="form-control product-input price-display"
                                        inputmode="numeric" autocomplete="off" placeholder="0"
                                        data-target="#add-price-raw">
                                    <input type="hidden" name="price" id="add-price-raw" class="price-raw"
                                        value="{{ (int) old('price', 0) }}">
                                </div>
                            </div>
                            <div class="product-field">
                                <label class="product-field-label">Stok awal <span class="req">*</span></label>
                                <input type="number" name="stock_quantity" class="form-control product-input"
                                    value="{{ old('stock_quantity', 0) }}" min="0" required>
                            </div>
                            <div class="product-field">
                                <label class="product-field-label">Min. stok <span class="req">*</span></label>
                                <input type="number" name="minimum_stock" class="form-control product-input"
                                    value="{{ old('minimum_stock', 0) }}" min="0" required>
                            </div>
                        </div>

                        <div class="product-field product-field-full">
                            <label class="product-field-label">Keterangan</label>
                            <textarea name="description" class="form-control product-input product-textarea">{{ old('description') }}</textarea>
                        </div>

                        <div class="product-field product-field-full">
                            <label class="product-field-label">Gambar produk</label>
                            <input type="file" name="image" class="form-control product-input"
                                accept="image/jpeg,image/png,image/jpg">
                        </div>
                    </form>
                </div>
                <div class="modal-footer product-form-footer">
                    <button type="button" class="btn product-form-btn-cancel" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="product-save-btn" form="add-product-form">Simpan Barang</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Edit Barang per item -->
    @foreach ($products as $product)
        <div class="modal fade product-form-modal" id="editProductModal-{{ $product->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content product-form-content">
                    <div class="modal-header product-form-header">
                        <h5 class="modal-title mb-0">Edit Barang</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body product-form-body">
                        <form action="{{ route('products.update', $product) }}" method="POST" enctype="multipart/form-data"
                            id="edit-product-form-{{ $product->id }}" class="product-form-grid js-product-ajax-form"
                            data-form-action="update" data-product-id="{{ $product->id }}"
                            data-modal-id="editProductModal-{{ $product->id }}">
                            @csrf
                            @method('PUT')
                            <div class="product-field product-field-full">
                                <label class="product-field-label">Nama barang <span class="req">*</span></label>
                                <input type="text" name="name" class="form-control product-input"
                                    value="{{ old('name', $product->name) }}" required>
                            </div>

                            <div class="product-field">
                                <label class="product-field-label">Kode barang</label>
                                <input type="text" name="barcode" class="form-control product-input"
                                    value="{{ old('barcode', $product->barcode) }}">
                            </div>

                            <div class="product-field">
                                <label class="product-field-label">Kategori <span class="req">*</span></label>
                                <select name="category_id" class="form-select product-input" required>
                                    <option value="">Pilih kategori</option>
                                    @foreach ($categories as $cat)
                                        <option value="{{ $cat->id }}"
                                            {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>
                                            {{ $cat->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="product-field">
                                <label class="product-field-label">Supplier <span class="text-muted fw-normal">(opsional)</span></label>
                                <select name="supplier_id" class="form-select product-input">
                                    <option value="">— Belum ditentukan —</option>
                                    @foreach ($suppliers as $sup)
                                        <option value="{{ $sup->id }}"
                                            {{ old('supplier_id', $product->supplier_id) == $sup->id ? 'selected' : '' }}>
                                            {{ $sup->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted d-block mt-1">Supplier baru? <a href="{{ route('suppliers.index') }}" target="_blank" rel="noopener">Kelola supplier</a>.</small>
                            </div>

                            <div class="product-field">
                                <label class="product-field-label">Satuan <span class="req">*</span></label>
                                <select name="unit" class="form-select product-input" required>
                                    <option value="kg" {{ old('unit', $product->unit) === 'kg' ? 'selected' : '' }}>Kg</option>
                                    <option value="liter" {{ old('unit', $product->unit) === 'liter' ? 'selected' : '' }}>Liter</option>
                                    <option value="karung" {{ old('unit', $product->unit) === 'karung' ? 'selected' : '' }}>Karung</option>
                                </select>
                            </div>

                            <div class="product-field">
                                <label class="product-field-label">Brand <span class="req">*</span></label>
                                <input type="text" name="brand" class="form-control product-input"
                                    value="{{ old('brand', $product->brand) }}" required>
                            </div>

                            <div class="product-field">
                                <label class="product-field-label">Jenis <span class="req">*</span></label>
                                <select name="type" class="form-select product-input" required>
                                    <option value="organik" {{ old('type', $product->type) === 'organik' ? 'selected' : '' }}>Organik</option>
                                    <option value="kimia" {{ old('type', $product->type) === 'kimia' ? 'selected' : '' }}>Kimia</option>
                                </select>
                            </div>

                            <div class="product-section-label">Stok & Harga</div>
                            <div class="product-triple-grid">
                                <div class="product-field">
                                    <label class="product-field-label">Harga jual <span class="req">*</span></label>
                                    <div class="price-input-wrap">
                                        <span class="price-prefix">Rp</span>
                                        <input type="text" class="form-control product-input price-display"
                                            inputmode="numeric" autocomplete="off" placeholder="0"
                                            data-target="#edit-price-raw-{{ $product->id }}">
                                        <input type="hidden" name="price" id="edit-price-raw-{{ $product->id }}"
                                            class="price-raw" value="{{ (int) old('price', $product->price) }}">
                                    </div>
                                </div>
                                <div class="product-field">
                                    <label class="product-field-label">Stok awal <span class="req">*</span></label>
                                    <input type="number" name="stock_quantity" class="form-control product-input"
                                        value="{{ old('stock_quantity', $product->stock_quantity) }}" min="0" required>
                                </div>
                                <div class="product-field">
                                    <label class="product-field-label">Min. stok <span class="req">*</span></label>
                                    <input type="number" name="minimum_stock" class="form-control product-input"
                                        value="{{ old('minimum_stock', $product->minimum_stock) }}" min="0" required>
                                </div>
                            </div>

                            <div class="product-field product-field-full">
                                <label class="product-field-label">Keterangan</label>
                                <textarea name="description" class="form-control product-input product-textarea">{{ old('description', $product->description) }}</textarea>
                            </div>

                            <div class="product-field product-field-full">
                                <label class="product-field-label d-block">Gambar produk</label>
                                @if ($product->image)
                                    <div class="mb-2">
                                        <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}"
                                            style="max-width: 140px; max-height: 140px; object-fit: cover; border-radius: 8px; border: 1px solid #e5e7eb;">
                                    </div>
                                @endif
                                <input type="file" name="image" class="form-control product-input"
                                    accept="image/jpeg,image/png,image/jpg">
                                @if ($product->image)
                                    <div class="form-check mt-2">
                                        <input class="form-check-input" type="checkbox" name="remove_image" value="1"
                                            id="remove-image-{{ $product->id }}">
                                        <label class="form-check-label" for="remove-image-{{ $product->id }}">
                                            Hapus gambar saat ini
                                        </label>
                                    </div>
                                @endif
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer product-form-footer">
                        <button type="button" class="btn product-form-btn-cancel" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="product-save-btn"
                            form="edit-product-form-{{ $product->id }}">Simpan Barang</button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endsection

@push('styles')
    <style>
        html {
            overflow-y: scroll;
        }

        body.products-page .ps-sidebar,
        body.products-page .ps-main,
        body.products-page .ps-content,
        body.products-page .ps-layout,
        body.products-page .ps-nav-item,
        body.products-page .ps-global-navbar {
            transition: none !important;
            animation: none !important;
        }

        body.products-page .ps-sidebar {
            top: 64px !important;
            height: calc(100vh - 64px) !important;
            overflow-y: auto !important;
        }

        body.products-page .ps-main {
            min-height: calc(100vh - 64px) !important;
        }

        .product-modern-page {
            --prod-ink: #1e1710;
            --prod-muted: #4a3728;
            --prod-bg: #f4f1ec;
            --prod-surface: #ede8e1;
            --prod-border: #c8bfb4;
            --prod-accent: #1a5c42;
            background: var(--prod-bg);
            color: var(--prod-ink);
            border: 0.5px solid var(--prod-border);
            border-radius: 12px;
            padding: 20px;
            font-size: 16px;
            line-height: 1.55;
        }

        .product-modern-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
            position: relative;
            z-index: 5;
        }

        .product-modern-title {
            margin: 0;
            font-size: 1.35rem;
            font-weight: 500;
            color: var(--prod-ink);
        }

        .product-modern-subtitle {
            margin: 6px 0 0;
            font-size: 15px;
            color: var(--prod-muted);
        }

        .product-modern-actions {
            display: flex;
            gap: 8px;
            position: relative;
            z-index: 8;
            flex-shrink: 0;
        }

        .btn-modern-outline,
        .btn-modern-primary {
            border-radius: 12px;
            font-size: 15px;
            font-weight: 500;
            padding: 10px 16px;
            min-height: 44px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: 0.5px solid var(--prod-border);
            background: var(--prod-bg) !important;
            color: var(--prod-ink) !important;
            opacity: 1 !important;
            visibility: visible !important;
            position: relative;
            z-index: 9;
            text-decoration: none !important;
            white-space: nowrap;
            line-height: 1.2;
        }

        .btn-modern-primary {
            border-color: var(--prod-accent) !important;
            background: var(--prod-accent) !important;
            color: #f8faf8 !important;
            min-height: 48px;
        }

        .btn-modern-outline:hover {
            background: rgba(46, 125, 94, 0.08) !important;
            border-color: var(--prod-accent);
            color: var(--prod-accent) !important;
        }

        .btn-modern-primary:hover {
            background: #144a35 !important;
            border-color: #144a35 !important;
            color: #f8faf8 !important;
        }

        .btn-modern-outline svg,
        .btn-modern-primary svg {
            width: 13px;
            height: 13px;
            flex-shrink: 0;
            opacity: 0.95;
        }

        .product-stats-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 10px;
            margin-bottom: 12px;
        }

        .product-stat-card {
            background: var(--prod-surface);
            border: 0.5px solid var(--prod-border);
            border-radius: 12px;
            padding: 14px 16px;
        }

        .stat-label {
            font-size: 14px;
            font-weight: 500;
            color: var(--prod-muted);
            margin-bottom: 6px;
        }

        .stat-value {
            font-size: 1.625rem;
            font-weight: 600;
            color: var(--prod-ink);
            line-height: 1.2;
            margin-bottom: 8px;
        }

        .stat-badge {
            display: inline-block;
            font-size: 13px;
            border-radius: 999px;
            padding: 3px 10px;
            font-weight: 500;
        }

        .stat-badge.success { background: rgba(46, 125, 94, 0.12); color: #1e4d3a; }
        .stat-badge.warning { background: rgba(186, 117, 23, 0.15); color: #7a4e0f; }

        .product-toolbar-form {
            display: flex;
            gap: 8px;
        }

        .product-search-wrap {
            flex: 1;
            min-width: 240px;
            position: relative;
        }

        .product-search-wrap svg {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--prod-muted);
            pointer-events: none;
            z-index: 1;
            flex-shrink: 0;
        }

        .product-search-wrap input,
        .toolbar-select .form-select {
            border: 0.5px solid var(--prod-border);
            border-radius: 12px;
            min-height: 44px;
            font-size: 16px;
            box-shadow: none !important;
            background: var(--prod-bg);
            color: var(--prod-ink);
        }

        .product-search-wrap input {
            width: 100%;
            padding: 10px 14px 10px 42px !important;
        }
        .toolbar-select { width: 200px; }

        .product-search-wrap input::placeholder {
            color: #4a3728;
            opacity: 0.85;
        }

        .product-search-wrap input:focus,
        .toolbar-select .form-select:focus {
            border-color: var(--prod-accent);
            box-shadow: 0 0 0 3px rgba(46, 125, 94, 0.18) !important;
        }

        .product-modern-table-wrap {
            background: var(--prod-surface);
            border: 0.5px solid var(--prod-border);
            border-radius: 12px;
            overflow: hidden;
        }

        .product-modern-table {
            --bs-table-bg: var(--prod-surface);
            --bs-table-striped-bg: var(--prod-surface);
            --bs-table-striped-color: var(--prod-ink);
            --bs-table-active-bg: var(--prod-bg);
            --bs-table-active-color: var(--prod-ink);
            --bs-table-hover-bg: var(--prod-bg);
            --bs-table-hover-color: var(--prod-ink);
            --bs-table-color: var(--prod-ink);
            color: var(--prod-ink) !important;
        }

        .product-modern-table thead th {
            background: var(--prod-bg);
            color: var(--prod-muted);
            border-bottom: 0.5px solid var(--prod-border);
            font-size: 13px;
            font-weight: 600;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            padding: 12px 14px;
        }

        .product-modern-table tbody td {
            border-bottom: 0.5px solid var(--prod-border);
            font-size: 15px;
            color: var(--prod-ink);
            vertical-align: middle;
            background: var(--prod-surface) !important;
            padding: 12px 14px;
        }

        .product-modern-table tbody tr {
            background: var(--prod-surface) !important;
        }

        .product-modern-table tbody tr:hover td {
            background: var(--prod-bg) !important;
        }

        .product-modern-table tbody .text-muted {
            color: var(--prod-muted) !important;
        }

        .code-chip {
            font-family: var(--font-sans);
            background: var(--prod-bg);
            color: var(--prod-ink);
            border: 0.5px solid var(--prod-border);
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
        }

        .product-name-cell {
            font-weight: 600;
            color: var(--prod-ink);
            font-size: 15px;
        }

        .price-cell {
            font-weight: 600;
            color: var(--prod-ink);
            font-size: 15px;
        }

        .product-modern-table .category-col {
            min-width: 7.5rem;
            vertical-align: middle;
        }

        .category-pill {
            display: inline-block;
            padding: 8px 12px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            line-height: 1.45;
            border: 1.5px solid transparent;
            white-space: normal;
            word-break: break-word;
            text-align: center;
            max-width: 9.5rem;
            overflow: visible;
        }

        .category-pill-pupuk,
        .category-pill-npk,
        .category-pill-kimia,
        .category-pill-herbisida {
            background: #d4ebe3;
            color: #1a5c42;
            border-color: #1a5c42;
        }

        .category-pill-alat {
            background: #f5e6c8;
            color: #7a4e0f;
            border-color: #ba7517;
        }

        .category-pill-pestisida {
            background: #ebe6e0;
            color: #1e1710;
            border-color: #6b5c4e;
        }

        .stock-cell { min-width: 140px; }
        .stock-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px; }
        .stock-top strong,
        .stock-top span {
            color: var(--prod-ink);
            font-weight: 600;
            font-size: 15px;
        }
        .stock-top small { color: var(--prod-muted); font-size: 13px; font-weight: 500; }
        .stock-progress-track { width: 56px; height: 6px; border-radius: 999px; background: rgba(60, 48, 32, 0.12); }
        .stock-progress-fill { height: 6px; border-radius: 999px; }
        .stock-progress-fill.safe { background: var(--prod-accent); }
        .stock-progress-fill.low { background: #ba7517; }

        .status-active {
            font-size: 14px;
            font-weight: 600;
            color: var(--prod-ink);
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .status-dot { width: 8px; height: 8px; border-radius: 50%; background: var(--prod-accent); display: inline-block; }

        .action-icon-btn {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            border: 2px solid transparent;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-left: 4px;
            vertical-align: middle;
        }
        .action-icon-btn .action-icon-svg {
            width: 20px;
            height: 20px;
            flex-shrink: 0;
        }
        .action-edit {
            color: #ffffff;
            border-color: #1d6fd4;
            background: #1d6fd4;
        }
        .action-edit:hover {
            background: #1558ab;
            border-color: #1558ab;
            color: #ffffff;
        }
        .action-edit:focus-visible {
            outline: 2px solid #1d6fd4;
            outline-offset: 2px;
        }
        .action-delete {
            color: #ffffff;
            border-color: #c62828;
            background: #c62828;
        }
        .action-delete .action-icon-svg {
            width: 22px;
            height: 22px;
        }
        .action-delete:hover {
            background: #a31f1f;
            border-color: #a31f1f;
            color: #ffffff;
        }
        .action-delete:focus-visible {
            outline: 2px solid #c62828;
            outline-offset: 2px;
        }

        .product-table-footer {
            padding: 12px 14px;
            display: flex;
            justify-content: space-between;
            border-top: 0.5px solid var(--prod-border);
            font-size: 14px;
            color: var(--prod-muted);
            background: var(--prod-surface);
        }

        .product-table-footer strong {
            color: var(--prod-ink);
        }

        :root {
            --ps-navbar-height: 64px;
        }

        .product-form-modal {
            z-index: 1055;
        }

        body.products-page .product-form-modal.modal {
            --bs-modal-bg: var(--prod-surface);
            --bs-modal-color: var(--prod-ink);
            --bs-modal-border-color: var(--prod-border);
            --bs-modal-header-border-color: var(--prod-border);
            --bs-modal-footer-bg: var(--prod-bg);
            --bs-modal-footer-border-color: var(--prod-border);
            --bs-modal-padding: 1rem;
        }

        .product-form-modal .modal-dialog {
            margin: calc(var(--ps-navbar-height) + 12px) auto 16px auto;
            max-width: min(920px, calc(100vw - 24px));
            max-height: calc(100vh - var(--ps-navbar-height) - 32px);
            display: flex;
        }

        .product-form-content {
            width: 100%;
            display: flex;
            flex-direction: column;
            max-height: calc(100vh - var(--ps-navbar-height) - 32px);
            overflow: hidden;
            border-radius: 12px;
            border: 1px solid var(--prod-border);
            background-color: var(--prod-surface) !important;
            color: var(--prod-ink);
            box-shadow: 0 12px 40px rgba(30, 23, 16, 0.18);
        }

        .product-form-header,
        .product-form-footer {
            flex-shrink: 0;
            border-color: var(--prod-border);
            background-color: var(--prod-bg) !important;
        }

        .product-form-header {
            padding: 14px 20px;
        }

        .product-form-footer {
            padding: 12px 20px !important;
        }

        body.products-page .product-form-modal .modal-footer.product-form-footer > * {
            margin: 0 !important;
        }

        .product-form-header .modal-title {
            font-size: 1.15rem;
            font-weight: 600;
            color: var(--prod-ink);
        }

        .product-form-body {
            flex: 1;
            overflow-y: auto;
            padding: 18px 20px 20px;
            background: var(--prod-surface);
            scrollbar-gutter: stable;
        }

        .product-form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px 14px;
        }

        .product-field-full,
        .product-section-label,
        .product-triple-grid {
            grid-column: 1 / -1;
        }

        .product-triple-grid {
            display: grid;
            gap: 12px 14px;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            align-items: start;
        }

        body.products-page .ps-content .product-form-modal .form-control,
        body.products-page .ps-content .product-form-modal .form-select {
            border: 1px solid var(--prod-border) !important;
            background-color: var(--prod-bg) !important;
            color: var(--prod-ink) !important;
            border-radius: 10px !important;
            min-height: 44px !important;
            font-size: 15px !important;
            box-shadow: none !important;
        }

        body.products-page .ps-content .product-form-modal .form-control:focus,
        body.products-page .ps-content .product-form-modal .form-select:focus {
            border-color: var(--prod-accent) !important;
            box-shadow: 0 0 0 3px rgba(26, 92, 66, 0.18) !important;
            background-color: var(--prod-bg) !important;
            color: var(--prod-ink) !important;
        }

        body.products-page .ps-content .product-form-modal .form-control::placeholder {
            color: var(--prod-muted) !important;
            opacity: 0.85;
        }

        body.products-page .ps-content .product-form-modal input[type="file"].form-control {
            padding: 0.5rem 0.75rem !important;
            line-height: 1.5;
        }

        body.products-page .ps-content .product-form-modal input[type="file"].form-control::file-selector-button,
        body.products-page .ps-content .product-form-modal input[type="file"].form-control::-webkit-file-upload-button {
            margin-right: 12px;
            padding: 0.4rem 0.85rem;
            border-radius: 8px;
            border: 1px solid var(--prod-accent);
            background: #d4ebe3;
            color: var(--prod-accent);
            font-weight: 600;
            font-size: 13px;
            cursor: pointer;
        }

        body.products-page .ps-content .product-form-modal input[type="file"].form-control::file-selector-button:hover {
            background: #c5e0d6;
        }

        body.products-page .product-form-modal .alert-danger {
            background: rgba(163, 45, 45, 0.1) !important;
            border: 1px solid rgba(163, 45, 45, 0.35) !important;
            color: #7a2020 !important;
            border-radius: 10px;
            font-size: 14px;
        }

        body.products-page .product-form-modal .alert-danger strong {
            color: #a32d2d;
        }

        body.products-page .product-form-modal .form-check-label {
            color: var(--prod-ink);
            font-size: 14px;
            font-weight: 500;
        }

        body.products-page .product-form-modal .form-check-input {
            border-color: var(--prod-border);
            background-color: var(--prod-bg);
        }

        body.products-page .product-form-modal .form-check-input:checked {
            background-color: var(--prod-accent);
            border-color: var(--prod-accent);
        }

        .product-field-label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: 600;
            color: var(--prod-ink);
        }

        .product-field-label .req {
            color: #a32d2d;
            font-weight: 700;
        }

        .product-section-label {
            margin-top: 6px;
            padding: 10px 14px;
            border-radius: 10px;
            border: 1px solid rgba(26, 92, 66, 0.35);
            background: #d4ebe3;
            font-size: 14px;
            font-weight: 600;
            color: var(--prod-accent);
        }

        .product-input {
            min-height: 44px;
            padding: 10px 12px;
            border-radius: 10px;
            border: 1px solid var(--prod-border);
            background: var(--prod-bg);
            color: var(--prod-ink);
        }

        .product-input:focus {
            border-color: var(--prod-accent);
            box-shadow: 0 0 0 3px rgba(26, 92, 66, 0.18);
            background: var(--prod-bg);
            color: var(--prod-ink);
        }

        body.products-page .product-form-modal .price-input-wrap {
            display: flex;
            align-items: stretch;
            min-height: 44px;
            border: 1.5px solid #a89888 !important;
            border-radius: 10px !important;
            background-color: #faf8f5 !important;
            overflow: hidden;
            box-shadow: inset 0 1px 2px rgba(30, 23, 16, 0.06);
        }

        body.products-page .product-form-modal .price-input-wrap:focus-within {
            border-color: var(--prod-accent) !important;
            box-shadow: 0 0 0 3px rgba(26, 92, 66, 0.18) !important;
            background-color: #faf8f5 !important;
        }

        body.products-page .product-form-modal .price-prefix {
            position: static;
            flex: 0 0 auto;
            display: flex;
            align-items: center;
            padding: 0 6px 0 14px;
            color: var(--prod-muted);
            font-size: 15px;
            font-weight: 600;
            pointer-events: none;
            user-select: none;
            border-right: 1px solid var(--prod-border);
            background: #ede8e1;
        }

        body.products-page .product-form-modal .price-input-wrap .price-display {
            flex: 1 1 auto;
            min-width: 0;
            min-height: 42px !important;
            border: none !important;
            border-radius: 0 !important;
            padding-left: 10px !important;
            padding-right: 12px !important;
            box-shadow: none !important;
            background: transparent !important;
        }

        body.products-page .product-form-modal .price-input-wrap .price-display:focus {
            border: none !important;
            box-shadow: none !important;
            outline: none !important;
        }

        .product-textarea {
            min-height: 64px;
            resize: vertical;
        }

        .product-form-footer {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .product-form-btn-cancel {
            border-radius: 10px !important;
            font-weight: 600 !important;
            padding: 10px 18px !important;
            min-height: 44px !important;
            color: var(--prod-ink) !important;
            border: 1px solid var(--prod-border) !important;
            background: var(--prod-bg) !important;
        }

        .product-form-btn-cancel:hover {
            background: #ebe6e0 !important;
            border-color: var(--prod-accent) !important;
            color: var(--prod-accent) !important;
        }

        /* Tailwind preflight membuat button transparan — pakai warna eksplisit + !important */
        body.products-page .product-form-modal button.product-save-btn[type="submit"],
        body.products-page .product-form-modal .product-save-btn {
            -webkit-appearance: none;
            appearance: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            background-color: #1a5c42 !important;
            background-image: none !important;
            border: 1.5px solid #144a35 !important;
            color: #f8faf8 !important;
            font-weight: 600 !important;
            font-size: 15px !important;
            padding: 10px 22px !important;
            min-height: 48px !important;
            min-width: 140px;
            border-radius: 10px !important;
            box-shadow: 0 3px 10px rgba(26, 92, 66, 0.28) !important;
            transform: none !important;
        }

        body.products-page .product-form-modal button.product-save-btn[type="submit"]:hover,
        body.products-page .product-form-modal .product-save-btn:hover {
            background-color: #144a35 !important;
            background-image: none !important;
            border-color: #0f3d2e !important;
            color: #f8faf8 !important;
            transform: none !important;
        }

        body.products-page .product-form-modal button.product-save-btn[type="submit"]:focus-visible,
        body.products-page .product-form-modal .product-save-btn:focus-visible {
            outline: none !important;
            box-shadow: 0 0 0 3px rgba(26, 92, 66, 0.35) !important;
        }

        .product-form-modal .btn-close {
            filter: invert(0.85);
            opacity: 0.75;
        }

        .product-form-modal .btn-close:hover {
            opacity: 1;
        }

        body.products-page .product-form-modal a {
            color: var(--prod-accent);
            font-weight: 500;
        }

        .product-toast {
            position: fixed;
            top: 1.25rem;
            right: 1.25rem;
            z-index: 1080;
            max-width: min(420px, calc(100vw - 2rem));
            padding: 0.85rem 1.1rem;
            border-radius: 10px;
            background: #1a5c42;
            color: #f8faf8;
            font-weight: 600;
            font-size: 15px;
            box-shadow: 0 8px 24px rgba(26, 92, 66, 0.35);
            transition: opacity 0.2s ease, transform 0.2s ease;
        }

        .product-toast.is-error {
            background: #9b2c2c;
            box-shadow: 0 8px 24px rgba(155, 44, 44, 0.35);
        }

        .product-toast[hidden] {
            display: none;
        }

        @media (max-width: 767.98px) {
            .product-stats-grid {
                grid-template-columns: 1fr 1fr;
            }

            .product-toolbar-form {
                flex-direction: column;
            }

            .toolbar-select {
                width: 100%;
            }

            .product-form-grid {
                grid-template-columns: 1fr;
            }

            .product-triple-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const hasErrors = {{ $errors->any() ? 'true' : 'false' }};
            if (hasErrors) {
                const modalEl = document.getElementById('addProductModal');
                if (modalEl) {
                    const modal = new bootstrap.Modal(modalEl);
                    modal.show();
                }
            }

            const params = new URLSearchParams(window.location.search);
            const editId = params.get('edit');
            if (editId) {
                const editModalEl = document.getElementById('editProductModal-' + editId);
                if (editModalEl) {
                    const editModal = new bootstrap.Modal(editModalEl);
                    editModal.show();
                }
            }

            const allowedControlKeys = new Set([
                'Backspace', 'Delete', 'Tab', 'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', 'Home', 'End'
            ]);

            function formatNumericInput(displayEl, rawEl) {
                const digits = (displayEl.value || '').replace(/\D/g, '');
                const number = digits ? parseInt(digits, 10) : 0;
                const oldLen = displayEl.value.length;
                const caretPos = displayEl.selectionStart || 0;

                displayEl.value = number ? number.toLocaleString('id-ID') : '';
                rawEl.value = number;

                const newLen = displayEl.value.length;
                const nextPos = Math.max(0, caretPos + (newLen - oldLen));
                try {
                    displayEl.setSelectionRange(nextPos, nextPos);
                } catch (_) {}
            }

            document.querySelectorAll('.price-display').forEach((displayEl) => {
                const targetSelector = displayEl.getAttribute('data-target');
                const rawEl = targetSelector ? document.querySelector(targetSelector) : null;
                if (!rawEl) return;

                const initial = parseInt(rawEl.value || '0', 10) || 0;
                displayEl.value = initial ? initial.toLocaleString('id-ID') : '';

                displayEl.addEventListener('keydown', (e) => {
                    if (e.key === '.' || e.key === ',') {
                        e.preventDefault();
                        return;
                    }

                    if (allowedControlKeys.has(e.key) || (e.ctrlKey || e.metaKey)) return;
                    if (!/^\d$/.test(e.key)) e.preventDefault();
                });

                displayEl.addEventListener('input', function() {
                    formatNumericInput(this, rawEl);
                });

                displayEl.addEventListener('paste', (e) => {
                    e.preventDefault();
                    const pasted = (e.clipboardData || window.clipboardData)?.getData('text') || '';
                    const digits = pasted.replace(/\D/g, '');
                    if (!digits) return;
                    displayEl.value = digits;
                    formatNumericInput(displayEl, rawEl);
                });
            });

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const toastEl = document.getElementById('product-toast');
            const flashSuccess = document.getElementById('product-flash-success');
            let toastTimer = null;

            function showProductToast(message, isError = false) {
                if (!toastEl) return;
                if (flashSuccess) flashSuccess.remove();
                toastEl.textContent = message;
                toastEl.classList.toggle('is-error', isError);
                toastEl.hidden = false;
                clearTimeout(toastTimer);
                toastTimer = setTimeout(() => {
                    toastEl.hidden = true;
                }, 4200);
            }

            function formatIdNumber(value) {
                return Number(value || 0).toLocaleString('id-ID');
            }

            function updateProductStats(stats) {
                if (!stats) return;
                const map = {
                    total_products: stats.total_products,
                    total_stock: stats.total_stock,
                    safe_stock_count: stats.safe_stock_count,
                    restock_count: stats.restock_count,
                };
                Object.entries(map).forEach(([key, value]) => {
                    const card = document.querySelector(`[data-stat="${key}"] [data-stat-value]`);
                    if (card) card.textContent = formatIdNumber(value);
                });
            }

            function updateProductRow(data) {
                const row = document.querySelector(`tr[data-product-id="${data.id}"]`);
                if (!row) return;

                const codeChip = row.querySelector('.code-chip');
                if (codeChip) codeChip.textContent = data.barcode;

                const nameCell = row.querySelector('.product-name-cell');
                if (nameCell) nameCell.textContent = data.name;

                const categoryPill = row.querySelector('.category-pill');
                if (categoryPill) {
                    categoryPill.textContent = data.category_name;
                    categoryPill.className = `category-pill ${data.category_class}`;
                }

                const unitCell = row.querySelector('td.text-muted');
                if (unitCell) unitCell.textContent = data.unit;

                const priceCell = row.querySelector('.price-cell');
                if (priceCell) priceCell.textContent = `Rp ${data.price_formatted}`;

                const stockTop = row.querySelector('.stock-top');
                if (stockTop) {
                    stockTop.innerHTML = `<span>${data.stock_quantity_formatted}</span><small>min ${data.minimum_stock_formatted}</small>`;
                }

                const progressFill = row.querySelector('.stock-progress-fill');
                if (progressFill) {
                    progressFill.style.width = `${data.progress}%`;
                    progressFill.classList.toggle('low', data.is_low);
                    progressFill.classList.toggle('safe', !data.is_low);
                }
            }

            function removeProductRow(productId) {
                const row = document.querySelector(`tr[data-product-id="${productId}"]`);
                if (row) row.remove();

                const modal = document.getElementById(`editProductModal-${productId}`);
                if (modal) modal.remove();

                const tbody = document.getElementById('product-table-body');
                if (tbody && !tbody.querySelector('tr[data-product-id]')) {
                    tbody.innerHTML = '<tr><td colspan="8" class="text-center py-4 text-muted">Tidak ada barang sesuai filter</td></tr>';
                }
            }

            function showFormErrors(form, errors) {
                form.querySelectorAll('.product-field-error').forEach((el) => el.remove());

                Object.entries(errors || {}).forEach(([field, messages]) => {
                    const input = form.querySelector(`[name="${field}"]`);
                    if (!input) return;
                    const fieldWrap = input.closest('.product-field') || input.parentElement;
                    if (!fieldWrap) return;
                    const errorEl = document.createElement('div');
                    errorEl.className = 'product-field-error text-danger small mt-1';
                    errorEl.textContent = Array.isArray(messages) ? messages[0] : String(messages);
                    fieldWrap.appendChild(errorEl);
                });
            }

            async function submitProductForm(form) {
                const submitBtn = document.querySelector(`button[form="${form.id}"]`);
                const originalText = submitBtn?.textContent || 'Simpan Barang';

                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.textContent = 'Menyimpan...';
                }

                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: new FormData(form),
                    });

                    const payload = await response.json().catch(() => ({}));

                    if (!response.ok) {
                        if (response.status === 422 && payload.errors) {
                            showFormErrors(form, payload.errors);
                        }
                        showProductToast(payload.message || 'Gagal menyimpan data barang.', true);
                        return;
                    }

                    const action = form.dataset.formAction;
                    if (action === 'update' && payload.data) {
                        updateProductRow(payload.data);
                    }

                    if (action === 'create') {
                        sessionStorage.setItem('productsScrollY', String(window.scrollY));
                        window.location.assign(window.location.pathname + window.location.search);
                        return;
                    }

                    const modalId = form.dataset.modalId;
                    if (modalId) {
                        const modalEl = document.getElementById(modalId);
                        const instance = modalEl ? bootstrap.Modal.getInstance(modalEl) : null;
                        instance?.hide();
                    }

                    updateProductStats(payload.stats);
                    showProductToast(payload.message || 'Berhasil disimpan.');
                } catch (_) {
                    showProductToast('Koneksi bermasalah. Periksa internet lalu coba lagi.', true);
                } finally {
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.textContent = originalText;
                    }
                }
            }

            document.querySelectorAll('.js-product-ajax-form').forEach((form) => {
                form.addEventListener('submit', (event) => {
                    event.preventDefault();
                    submitProductForm(form);
                });
            });

            document.querySelectorAll('.js-product-delete-form').forEach((form) => {
                form.addEventListener('submit', async (event) => {
                    event.preventDefault();
                    if (!confirm('Yakin hapus produk ini?')) return;

                    const productId = form.dataset.productId;
                    const deleteBtn = form.querySelector('button[type="submit"]');
                    if (deleteBtn) deleteBtn.disabled = true;

                    try {
                        const response = await fetch(form.action, {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                            },
                            body: new FormData(form),
                        });

                        const payload = await response.json().catch(() => ({}));

                        if (!response.ok) {
                            showProductToast(payload.message || 'Gagal menghapus barang.', true);
                            return;
                        }

                        removeProductRow(productId);
                        updateProductStats(payload.stats);

                        const countEl = document.getElementById('product-table-count');
                        if (countEl) {
                            const tbody = document.getElementById('product-table-body');
                            const visible = tbody?.querySelectorAll('tr[data-product-id]').length || 0;
                            const total = payload.stats?.total_products ?? visible;
                            countEl.textContent = `Menampilkan ${visible} dari ${formatIdNumber(total)} barang`;
                        }

                        showProductToast(payload.message || 'Barang berhasil dihapus.');
                    } catch (_) {
                        showProductToast('Koneksi bermasalah. Periksa internet lalu coba lagi.', true);
                    } finally {
                        if (deleteBtn) deleteBtn.disabled = false;
                    }
                });
            });

            const savedScroll = sessionStorage.getItem('productsScrollY');
            if (savedScroll) {
                sessionStorage.removeItem('productsScrollY');
                window.scrollTo(0, parseInt(savedScroll, 10) || 0);
            }

            const toolbarForm = document.querySelector('.product-toolbar-form');
            if (toolbarForm) {
                toolbarForm.querySelectorAll('select').forEach((select) => {
                    select.addEventListener('change', () => toolbarForm.submit());
                });

                const searchInput = toolbarForm.querySelector('input[name="q"]');
                let searchTimer = null;
                if (searchInput) {
                    searchInput.addEventListener('keydown', (event) => {
                        if (event.key === 'Enter') {
                            event.preventDefault();
                            toolbarForm.submit();
                        }
                    });
                    searchInput.addEventListener('input', () => {
                        clearTimeout(searchTimer);
                        searchTimer = setTimeout(() => toolbarForm.submit(), 500);
                    });
                }
            }
        });
    </script>
@endpush




