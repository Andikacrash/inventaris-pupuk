@extends('layouts.app')

@section('content')
    <div class="pos-container pos-sales-page">
        <!-- Main Content - Split Layout -->
        <div class="pos-main-layout">
            <!-- Left: Products Section -->
            <div class="pos-products-area">
                <div class="pos-sales-heading-row">
                    <button type="button" class="pos-sales-sidebar-trigger d-lg-none" onclick="toggleSidebar()"
                        aria-label="Buka menu navigasi">
                        <i class="fas fa-bars-staggered" aria-hidden="true"></i>
                    </button>
                    <div class="cashier-inline-title">Kasir</div>
                </div>

                <div class="products-toolbar-section">
                    <div class="products-search pos-search-wrap">
                        <span class="pos-search-icon" aria-hidden="true"><i class="fas fa-search"></i></span>
                        <input type="text" id="product-search" class="pos-search-input"
                            placeholder="Cari produk, kategori, atau kode barcode..." autocomplete="off">
                    </div>

                    <div class="category-toolbar-row">
                        <div class="pos-category-toolbar-main">
                            <div class="pos-category-tabs" id="pos-category-tabs" role="tablist"
                                aria-label="Kelompok produk">
                                <button type="button" role="tab" class="pos-cat-tab active" data-pos-group="all"
                                    aria-selected="true">
                                    <i class="fas fa-th pos-cat-tab__ico" aria-hidden="true"></i>
                                    <span class="pos-cat-tab__label">Semua</span>
                                    <span class="pos-cat-tab__badge" data-badge-for="all">0</span>
                                </button>
                                <button type="button" role="tab" class="pos-cat-tab" data-pos-group="pupuk"
                                    aria-selected="false">
                                    <i class="fas fa-leaf pos-cat-tab__ico" aria-hidden="true"></i>
                                    <span class="pos-cat-tab__label">Pupuk</span>
                                    <span class="pos-cat-tab__badge" data-badge-for="pupuk">0</span>
                                </button>
                                <button type="button" role="tab" class="pos-cat-tab" data-pos-group="herbisida"
                                    aria-selected="false">
                                    <i class="fas fa-spray-can pos-cat-tab__ico" aria-hidden="true"></i>
                                    <span class="pos-cat-tab__label">Herbisida</span>
                                    <span class="pos-cat-tab__badge" data-badge-for="herbisida">0</span>
                                </button>
                                <button type="button" role="tab" class="pos-cat-tab" data-pos-group="pestisida"
                                    aria-selected="false">
                                    <i class="fas fa-bug pos-cat-tab__ico" aria-hidden="true"></i>
                                    <span class="pos-cat-tab__label">Pestisida</span>
                                    <span class="pos-cat-tab__badge" data-badge-for="pestisida">0</span>
                                </button>
                            </div>
                        </div>
                        <div class="pos-view-toggle" role="group" aria-label="Tampilan produk">
                            <button type="button" class="pos-view-btn active" id="btn-view-grid"
                                aria-pressed="true" aria-label="Tampilan kotak">
                                <i class="fas fa-th" aria-hidden="true"></i>
                                <span class="pos-view-btn__label">Kotak</span>
                            </button>
                            <button type="button" class="pos-view-btn" id="btn-view-list"
                                aria-pressed="false" aria-label="Tampilan daftar">
                                <i class="fas fa-list" aria-hidden="true"></i>
                                <span class="pos-view-btn__label">Daftar</span>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="products-list-section">
                    <p class="products-filter-count" id="products-filter-count" aria-live="polite">0 produk ditemukan</p>
                    <div class="pos-products-view pos-products-view--grid" id="products-grid">
                        <div class="loading-products">Memuat produk...</div>
                    </div>

                    <!-- Load More Button -->
                    <div class="load-more-wrapper" id="load-more-wrapper" style="display: none;">
                        <button type="button" class="load-more-btn" onclick="loadMoreProducts()">
                            Muat Lebih Banyak
                        </button>
                    </div>
                </div>
            </div>

            <!-- Right: Cart Sidebar -->
            <div class="pos-cart-sidebar">
                <div class="cart-sidebar-card">
                    <div class="cart-sidebar-header">
                        <div>
                            <h2 class="cart-sidebar-title">Ringkasan Pembayaran</h2>
                            <p class="cart-sidebar-subtitle">Item dipilih</p>
                        </div>
                        <span class="cart-count-badge" id="cart-count-badge">0</span>
                    </div>
                    <div class="cart-sidebar-body">
                        <div id="cart-items-sidebar" class="cart-items-sidebar">
                            <div class="empty-cart-sidebar">
                                <div class="empty-icon">🛒</div>
                                <p>Keranjang masih kosong</p>
                                <p class="empty-hint">Klik produk untuk menambahkannya</p>
                            </div>
                        </div>
                    </div>
                    <div class="cart-sidebar-footer">
                        <div class="cart-summary">
                            <div class="summary-row">
                                <span>Subtotal:</span>
                                <span id="cart-subtotal">Rp 0</span>
                            </div>
                            <div class="summary-row discount-row">
                                <div class="discount-input-group">
                                    <label>Tawar (Rp):</label>
                                    <input type="number" id="cart-discount" class="discount-input-small" value="0"
                                        min="0" step="1000" onchange="updateCartSummary()">
                                </div>
                                <span id="cart-discount-amount">Rp 0</span>
                            </div>
                            <div class="summary-row total-row">
                                <span>Total:</span>
                                <span id="cart-total-sidebar">Rp 0</span>
                            </div>
                        </div>
                        <button type="button" class="checkout-btn" onclick="openSidebarPayment()" id="checkout-btn"
                            disabled>
                            Lanjut ke Pembayaran
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 3: Pembayaran (Full Screen) -->
        <div class="pos-section" id="step-3-section" style="display: none;">
            <div class="pos-card payment-card">
                <div class="card-header-custom">
                    <span class="card-icon">💳</span>
                    <h2>Informasi Pembayaran</h2>
                </div>
                <div class="card-body-custom">
                    <form id="checkout-form">
                        <!-- Customer Info -->
                        <div class="form-group">
                            <label class="form-label">
                                <span class="label-icon">👤</span>
                                Nama Pelanggan (Opsional)
                            </label>
                            <input type="text" id="customer" class="form-input" placeholder="Nama pelanggan">
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                <span class="label-icon">📞</span>
                                No. Telepon
                            </label>
                            <input type="text" id="customer_phone" class="form-input" placeholder="08xxxxxxxxxx">
                        </div>

                        <!-- Delivery Method -->
                        <div class="form-group">
                            <label class="form-label">
                                <span class="label-icon">🚚</span>
                                Metode Pengiriman
                            </label>
                            <div class="delivery-options">
                                <label class="delivery-option">
                                    <input type="radio" name="delivery_method" value="pickup" checked>
                                    <span class="option-content">
                                        <span class="option-icon">🏪</span>
                                        <span class="option-text">Ambil di Toko</span>
                                    </span>
                                </label>
                                <label class="delivery-option">
                                    <input type="radio" name="delivery_method" value="delivery">
                                    <span class="option-content">
                                        <span class="option-icon">🚚</span>
                                        <span class="option-text">Diantar</span>
                                    </span>
                                </label>
                            </div>
                        </div>

                        <div class="form-group" id="delivery-info" style="display: none;">
                            <label class="form-label">
                                <span class="label-icon">📍</span>
                                Alamat Pengiriman
                            </label>
                            <textarea id="delivery_address" class="form-input" rows="3" placeholder="Masukkan alamat lengkap"></textarea>
                            <input type="text" id="delivery_phone" class="form-input mt-2"
                                placeholder="No. telepon pengiriman">
                        </div>

                        <!-- Pricing Summary -->
                        <div class="pricing-summary">
                            <div class="price-row">
                                <span>Subtotal:</span>
                                <span id="subtotal-display">Rp 0</span>
                            </div>
                            <div class="price-row discount-row">
                                <div class="discount-input-wrapper">
                                    <label>Tawar (Rp):</label>
                                    <input type="number" id="discount" class="discount-input" value="0"
                                        min="0" step="1000">
                                </div>
                                <span id="discount-amount">Rp 0</span>
                            </div>
                            <div class="price-row total-row">
                                <span>Total:</span>
                                <span id="total-display">Rp 0</span>
                            </div>
                        </div>

                        <!-- Payment -->
                        <div class="form-group">
                            <label class="form-label">
                                <span class="label-icon">💵</span>
                                Jumlah Pembayaran
                            </label>
                            <input type="text" id="payment" class="form-input payment-input" placeholder="0"
                                inputmode="numeric" autocomplete="off">
                            <div class="quick-payment-buttons">
                                <button type="button" class="quick-btn" onclick="setQuickPayment(0.5)">50%</button>
                                <button type="button" class="quick-btn" onclick="setQuickPayment(0.75)">75%</button>
                                <button type="button" class="quick-btn" onclick="setQuickPayment(1)">100%</button>
                            </div>
                        </div>

                        <!-- Change & Debt -->
                        <div class="payment-result">
                            <div class="result-item change-item" id="change-display" style="display: none;">
                                <span class="result-label">💰 Kembalian:</span>
                                <span class="result-value change-value" id="change-amount">Rp 0</span>
                            </div>
                            <div class="result-item debt-item" id="debt-display" style="display: none;">
                                <span class="result-label">⚠️ Sisa Hutang:</span>
                                <span class="result-value debt-value" id="debt-amount">Rp 0</span>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="payment-actions">
                            <button type="button" class="back-btn" onclick="goBackToProducts()">
                                ← Kembali ke Produk
                            </button>
                            <button type="submit" class="submit-btn" id="submit-btn">
                                <span class="btn-icon">✅</span>
                                Simpan Transaksi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Step 4: Success -->
        <div class="pos-section" id="step-4-section" style="display: none;">
            <div class="pos-card success-card">
                <div class="success-content">
                    <div class="success-icon">✅</div>
                    <h2>Transaksi Berhasil!</h2>
                    <p class="success-message">Transaksi telah disimpan dengan sukses</p>
                    <button type="button" class="new-transaction-btn" onclick="startNewTransaction()">
                        Transaksi Baru
                    </button>
                </div>
            </div>
        </div>

        <!-- Sidebar Payment Modal -->
        <div id="sidebar-payment-modal" class="sidebar-payment-modal" style="display: none;">
            <div class="sidebar-payment-dialog">
                <div class="sidebar-payment-header">
                    <h3>Pembayaran</h3>
                    <button type="button" class="sidebar-payment-close" onclick="closeSidebarPayment()">×</button>
                </div>
                <div class="sidebar-payment-body">
                    <div class="sidebar-customer-box">
                        <span class="label">Data pelanggan</span>
                        <input type="text" id="sidebar-customer-name" class="sidebar-customer-input"
                            placeholder="Nama pelanggan (opsional)">
                        <input type="text" id="sidebar-customer-phone" class="sidebar-customer-input"
                            placeholder="No. telepon (opsional)">
                    </div>
                    <div class="sidebar-payment-total-box">
                        <span class="label">Total yang harus dibayar</span>
                        <span class="value" id="sidebar-payment-total">Rp 0</span>
                    </div>
                    <div class="sidebar-delivery-methods">
                        <span class="label">Metode pengantaran</span>
                        <div class="sidebar-delivery-buttons">
                            <button type="button" class="sidebar-delivery-btn active" data-method="pickup">
                                Ambil di Toko
                            </button>
                            <button type="button" class="sidebar-delivery-btn" data-method="delivery">
                                Diantar
                            </button>
                        </div>
                        <div class="sidebar-delivery-fields" id="sidebar-delivery-fields" style="display: none;">
                            <textarea id="sidebar-delivery-address" class="sidebar-delivery-input" rows="2"
                                placeholder="Alamat pengantaran"></textarea>
                            <input type="text" id="sidebar-delivery-phone" class="sidebar-delivery-input"
                                placeholder="No. telepon penerima">
                            <div class="row g-2 m-0">
                                <div class="col-6 p-0 pe-1">
                                    <select id="sidebar-delivery-level" class="sidebar-delivery-input">
                                        <option value="hemat">Level Hemat</option>
                                        <option value="reguler" selected>Level Reguler</option>
                                        <option value="express">Level Express</option>
                                    </select>
                                </div>
                                <div class="col-6 p-0 ps-1">
                                    <input type="number" id="sidebar-delivery-distance" class="sidebar-delivery-input"
                                        min="0" step="0.1" placeholder="Jarak (km)">
                                </div>
                            </div>
                            <div class="row g-2 m-0 mt-2 align-items-center">
                                <div class="col-12 p-0 d-flex align-items-center justify-content-between gap-2">
                                    <label class="small m-0 sidebar-delivery-fee-label">
                                        <input type="checkbox" id="sidebar-delivery-fee-enabled" checked
                                            style="margin-right:6px; transform: translateY(1px);" />
                                        Pakai ongkir
                                    </label>
                                    <span class="small text-muted" id="sidebar-delivery-fee-suggest">Saran: Rp 0</span>
                                </div>
                                <div class="col-12 p-0">
                                    <input type="number" id="sidebar-delivery-fee-input" class="sidebar-delivery-input"
                                        min="0" step="1000" placeholder="Ongkir (Rp) — bisa diubah kasir">
                                </div>
                            </div>
                            <div class="small text-muted" id="sidebar-delivery-fee">Ongkir dipakai: Rp 0</div>
                        </div>
                    </div>
                    <div class="sidebar-payment-methods">
                        <span class="label">Metode pembayaran</span>
                        <div class="sidebar-payment-method-buttons">
                            <button type="button" class="sidebar-method-btn active" data-method="cash">Tunai</button>
                            <button type="button" class="sidebar-method-btn" data-method="transfer">Transfer</button>
                        </div>
                    </div>
                    <div class="sidebar-payment-amount">
                        <span class="label">Jumlah bayar</span>
                        <input type="text" id="sidebar-payment-amount" class="sidebar-amount-input" placeholder="0"
                            inputmode="numeric" autocomplete="off">
                        <div class="sidebar-quick-buttons">
                            <button type="button" onclick="setSidebarQuickPayment(50000)">Rp 50.000</button>
                            <button type="button" onclick="setSidebarQuickPayment(100000)">Rp 100.000</button>
                            <button type="button" onclick="setSidebarQuickPayment(150000)">Rp 150.000</button>
                            <button type="button" onclick="setSidebarQuickPayment(200000)">Rp 200.000</button>
                        </div>
                        <div class="sidebar-payment-info" id="sidebar-payment-info">
                            <!-- info kembalian / kurang bayar akan muncul di sini -->
                        </div>
                    </div>
                </div>
                <div class="sidebar-payment-footer">
                    <button type="button" class="sidebar-payment-primary" onclick="submitSidebarPayment()">
                        Simpan Pembayaran
                    </button>
                    <button type="button" class="sidebar-payment-secondary" onclick="closeSidebarPayment()">
                        Batal
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert Box -->
    <div id="alert-box" class="alert-container"></div>

    <!-- Credit Confirmation Modal (bukan class modal-content — hindari latar putih dari pos-custom.css) -->
    <div id="credit-modal" class="modal-overlay pos-themed-overlay" style="display: none;">
        <div class="pos-themed-dialog" role="dialog" aria-modal="true" aria-labelledby="credit-modal-title">
            <div class="pos-themed-dialog__header">
                <span class="pos-themed-dialog__emoji" aria-hidden="true">⚠️</span>
                <div>
                    <h3 id="credit-modal-title" class="pos-themed-dialog__title">Konfirmasi piutang</h3>
                    <p class="pos-themed-dialog__hint">Pembayaran lebih kecil dari total transaksi.</p>
                </div>
            </div>
            <div class="pos-themed-dialog__body">
                <p class="pos-themed-dialog__text">Simpan transaksi ini sebagai piutang? Anda akan mengisi data pelanggan
                    dan tanggal jatuh tempo pada langkah berikutnya.</p>
            </div>
            <div class="pos-themed-dialog__footer">
                <button type="button" class="pos-themed-btn pos-themed-btn--ghost" onclick="closeCreditModal()">Batal</button>
                <button type="button" class="pos-themed-btn pos-themed-btn--primary" onclick="confirmCredit()">Lanjutkan</button>
            </div>
        </div>
    </div>

    <div id="debt-customer-modal" class="modal-overlay pos-themed-overlay" style="display: none;">
        <div class="pos-themed-dialog" role="dialog" aria-modal="true" aria-labelledby="debt-customer-title">
            <div class="pos-themed-dialog__header">
                <span class="pos-themed-dialog__emoji" aria-hidden="true">👤</span>
                <div>
                    <h3 id="debt-customer-title" class="pos-themed-dialog__title">Data pelanggan &amp; jatuh tempo</h3>
                    <p class="pos-themed-dialog__hint">Wajib untuk mencatat piutang dengan benar.</p>
                </div>
            </div>
            <div class="pos-themed-dialog__body">
                <label class="pos-themed-label" for="debt-modal-customer-name">Nama pelanggan <span
                        class="pos-themed-req">*</span></label>
                <input type="text" id="debt-modal-customer-name" class="pos-themed-input" placeholder="Nama lengkap"
                    autocomplete="name" />

                <label class="pos-themed-label" for="debt-modal-customer-phone">No. telepon</label>
                <input type="text" id="debt-modal-customer-phone" class="pos-themed-input" placeholder="08…"
                    inputmode="tel" autocomplete="tel" />

                <label class="pos-themed-label" for="debt-modal-due-date">Jatuh tempo <span
                        class="pos-themed-req">*</span></label>
                <input type="date" id="debt-modal-due-date" class="pos-themed-input" />
            </div>
            <div class="pos-themed-dialog__footer">
                <button type="button" class="pos-themed-btn pos-themed-btn--ghost"
                    onclick="closeDebtCustomerModal()">Batal</button>
                <button type="button" class="pos-themed-btn pos-themed-btn--primary"
                    onclick="submitDebtCustomerModal()">Simpan transaksi</button>
            </div>
        </div>
    </div>

    <!-- Popup sukses penjualan -->
    <div id="sale-success-modal" class="sale-success-modal" style="display: none;">
        <div class="sale-success-card">
            <div class="sale-success-icon">✓</div>
            <div class="sale-success-title">Penjualan berhasil dicatat</div>
            <div class="sale-success-text">Data transaksi sudah tersimpan di sistem.</div>
        </div>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ url('css/pos-custom.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ url('css/pos-products-grid.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ url('css/pos-cart-sidebar.css') }}?v={{ time() }}">
    <style>
        /* Layout utama POS — ikut tema --ps-* (sama seperti halaman lain) */
        body {
            background: var(--ps-bg);
        }

        body .pos-container {
            margin: 0 auto !important;
            padding: 0 !important;
            background: transparent !important;
            max-width: 1600px !important;
            border-radius: 0;
            box-shadow: none;
        }

        .ps-content {
            padding: 8px 12px !important;
        }

        .pos-sales-heading-row {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: -6px 0 8px 0;
        }

        .pos-sales-sidebar-trigger {
            flex-shrink: 0;
            width: 40px;
            height: 40px;
            display: none;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            border: 1px solid var(--ps-border);
            background: var(--ps-surface);
            color: var(--ps-text);
            cursor: pointer;
        }

        @media (max-width: 991.98px) {
            .pos-sales-sidebar-trigger {
                display: inline-flex;
            }
        }

        .cashier-inline-title {
            font-size: 1.35rem;
            font-weight: 700;
            color: var(--ps-text);
            margin: 0;
            line-height: 1.15;
        }

        .products-toolbar-section {
            background: var(--ps-surface);
            border: 1px solid var(--ps-border);
            border-radius: 14px;
            padding: 14px;
            margin-bottom: 10px;
            box-shadow: none;
        }

        .products-list-section {
            background: var(--ps-surface);
            border: 1px solid var(--ps-border);
            border-radius: 14px;
            padding: 14px;
            box-shadow: none;
        }

        .products-section {
            background: transparent !important;
            border-radius: 0 !important;
            padding: 0 !important;
            box-shadow: none !important;
            margin-bottom: 0 !important;
        }

        .products-toolbar-section .products-search {
            margin-bottom: 12px;
        }

        .products-toolbar-section .pos-category-toolbar-main {
            flex: 1;
            min-width: 0;
        }

        .products-list-section .pos-products-view {
            min-height: 280px;
        }

        /* Layout kiri-kanan POS seperti contoh */
        .pos-main-layout {
            display: grid;
            grid-template-columns: minmax(0, 1fr);
            gap: 10px;
            margin-top: 0;
            align-items: start;
        }

        .pos-main-layout.has-cart {
            grid-template-columns: minmax(0, 2.9fr) minmax(320px, 1.1fr);
            gap: 18px;
        }

        @media (max-width: 992px) {
            .pos-main-layout {
                grid-template-columns: 1fr;
            }

            .pos-main-layout.has-cart {
                grid-template-columns: 1fr;
            }
        }

        /* Cart sidebar title */
        .cart-sidebar-title {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 700;
        }

        .cart-sidebar-subtitle {
            margin: 2px 0 0 0;
            font-size: 0.8rem;
            color: var(--ps-muted);
        }

        #cart-subtotal,
        .summary-row span:last-child {
            color: var(--ps-text);
        }

        #cart-discount-amount {
            color: rgba(248, 113, 113, 0.8);
        }

        /* Sidebar payment modal — kontras & ukuran teks untuk pengguna 50+ */
        .sidebar-payment-modal {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.45);
            display: flex;
            justify-content: flex-end;
            align-items: stretch;
            z-index: 1050;
        }

        .sidebar-payment-dialog {
            --payment-on-accent: var(--wn-on-accent, #f8faf8);
            width: 380px;
            max-width: 100%;
            background: var(--ps-surface);
            box-shadow: -8px 0 20px rgba(0, 0, 0, 0.35);
            display: flex;
            flex-direction: column;
        }

        .sidebar-payment-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 20px;
            border-bottom: 1px solid var(--ps-border);
            color: var(--ps-text);
        }

        .sidebar-payment-header h3 {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--ps-text);
        }

        .sidebar-payment-close {
            border: none;
            background: transparent;
            font-size: 1.4rem;
            line-height: 1;
            cursor: pointer;
            color: var(--ps-muted);
        }

        .sidebar-payment-body {
            padding: 16px 20px;
            flex: 1;
            overflow-y: auto;
        }

        .sidebar-payment-body .text-muted {
            color: var(--ps-text) !important;
            opacity: 0.9;
            font-size: 0.95rem;
        }

        .sidebar-payment-total-box {
            background: var(--ps-bg);
            border: 1px solid var(--ps-border);
            border-radius: 12px;
            padding: 12px 14px;
            margin-bottom: 16px;
        }

        .sidebar-customer-box {
            margin-bottom: 16px;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .sidebar-customer-box .label,
        .sidebar-delivery-methods > .label,
        .sidebar-payment-methods .label,
        .sidebar-payment-amount .label,
        .sidebar-payment-total-box .label {
            display: block;
            font-size: 1rem;
            font-weight: 600;
            color: var(--ps-text);
            margin-bottom: 6px;
            letter-spacing: 0.01em;
        }

        .sidebar-customer-input {
            width: 100%;
            padding: 10px 12px;
            border-radius: 8px;
            border: 1.5px solid var(--ps-border);
            font-size: 1rem;
            background: var(--ps-bg);
            color: var(--ps-text);
        }

        .sidebar-payment-total-box .value {
            font-size: 1.45rem;
            font-weight: 700;
            color: var(--ps-accent);
        }

        .sidebar-delivery-methods {
            margin-bottom: 16px;
        }

        .sidebar-delivery-buttons {
            display: flex;
            gap: 8px;
            margin-top: 6px;
        }

        .sidebar-delivery-btn {
            flex: 1;
            padding: 10px 12px;
            border-radius: 999px;
            border: 2px solid var(--ps-border);
            background: var(--ps-bg);
            font-size: 1rem;
            font-weight: 600;
            color: var(--ps-text);
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .sidebar-delivery-btn.active {
            background: var(--ps-accent);
            border-color: var(--ps-accent);
            color: var(--payment-on-accent);
            box-shadow: 0 2px 8px rgba(26, 92, 66, 0.25);
        }

        .sidebar-delivery-fields {
            margin-top: 10px;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .sidebar-delivery-input {
            width: 100%;
            padding: 10px 12px;
            border-radius: 8px;
            border: 1.5px solid var(--ps-border);
            font-size: 1rem;
            background: var(--ps-bg);
            color: var(--ps-text);
        }

        .sidebar-delivery-fee-label {
            color: var(--ps-text) !important;
            font-size: 0.95rem;
            font-weight: 500;
        }

        .sidebar-payment-method-buttons {
            display: flex;
            gap: 8px;
            margin-bottom: 16px;
        }

        .sidebar-method-btn {
            flex: 1;
            padding: 10px 12px;
            border-radius: 999px;
            border: 2px solid var(--ps-border);
            background: var(--ps-bg);
            font-size: 1rem;
            font-weight: 600;
            color: var(--ps-text);
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .sidebar-method-btn.active {
            background: var(--ps-accent);
            border-color: var(--ps-accent);
            color: var(--payment-on-accent);
            box-shadow: 0 2px 8px rgba(26, 92, 66, 0.25);
        }

        .sidebar-amount-input {
            width: 100%;
            padding: 12px 14px;
            border-radius: 8px;
            border: 2px solid var(--ps-border);
            font-size: 1.15rem;
            font-weight: 600;
            margin-bottom: 10px;
            background: var(--ps-bg);
            color: var(--ps-text);
        }

        .sidebar-quick-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .sidebar-quick-buttons button {
            flex: 1 1 calc(50% - 4px);
            padding: 9px 10px;
            border-radius: 999px;
            border: 2px solid var(--ps-border);
            background: var(--ps-bg);
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            color: var(--ps-text);
        }

        .sidebar-quick-buttons button:hover {
            border-color: var(--ps-accent);
            color: var(--ps-accent);
        }

        .sidebar-payment-info {
            margin-top: 10px;
            font-size: 1rem;
            font-weight: 500;
            color: var(--ps-text);
        }

        .sidebar-payment-info.success {
            color: var(--ps-accent);
        }

        .sidebar-payment-info.warning {
            color: #fb923c;
        }

        .sidebar-payment-footer {
            padding: 12px 20px 16px 20px;
            border-top: 1px solid var(--ps-border);
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .sidebar-payment-primary {
            padding: 14px;
            border-radius: 10px;
            border: none;
            background: var(--ps-accent);
            color: var(--payment-on-accent);
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            letter-spacing: 0.02em;
            box-shadow: 0 4px 12px rgba(26, 92, 66, 0.3);
        }

        .sidebar-payment-primary:hover {
            filter: brightness(1.08);
        }

        .sidebar-payment-secondary {
            padding: 12px;
            border-radius: 10px;
            border: 2px solid var(--ps-border);
            background: var(--ps-bg);
            font-size: 1rem;
            font-weight: 600;
            color: var(--ps-text);
            cursor: pointer;
        }

        /* Popup sukses di tengah */
        .sale-success-modal {
            position: fixed;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(15, 23, 42, 0.45);
            z-index: 1100;
        }

        .sale-success-card {
            background: var(--ps-surface);
            border: 1px solid var(--ps-border);
            border-radius: 14px;
            padding: 20px 24px;
            box-shadow: 0 20px 40px rgba(15, 23, 42, 0.35);
            text-align: center;
            max-width: 320px;
        }

        .sale-success-icon {
            width: 40px;
            height: 40px;
            border-radius: 999px;
            background: color-mix(in srgb, var(--ps-accent) 18%, transparent);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--ps-accent);
            font-size: 1.4rem;
            margin: 0 auto 10px auto;
        }

        .sale-success-title {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 4px;
            color: var(--ps-text);
        }

        .sale-success-text {
            font-size: 0.9rem;
            color: var(--ps-muted);
        }

        /* Dialog piutang — Warm Neutral */
        .pos-themed-overlay {
            z-index: 1200;
            background: rgba(60, 48, 32, 0.45) !important;
            backdrop-filter: blur(4px);
        }

        .pos-themed-dialog {
            width: 100%;
            max-width: 420px;
            margin: 16px;
            background: var(--ps-surface) !important;
            border: 1px solid var(--ps-border) !important;
            border-radius: 14px !important;
            box-shadow: 0 24px 48px rgba(0, 0, 0, 0.45) !important;
            overflow: hidden;
            color: var(--ps-text);
        }

        .pos-themed-dialog__header {
            display: flex;
            gap: 12px;
            align-items: flex-start;
            padding: 18px 18px 14px;
            border-bottom: 1px solid var(--ps-border);
            background: var(--ps-bg);
        }

        .pos-themed-dialog__emoji {
            font-size: 1.5rem;
            line-height: 1;
        }

        .pos-themed-dialog__title {
            margin: 0 0 4px 0;
            font-size: 1.05rem;
            font-weight: 700;
            color: var(--ps-text);
        }

        .pos-themed-dialog__hint {
            margin: 0;
            font-size: 0.82rem;
            color: var(--ps-muted);
            line-height: 1.35;
        }

        .pos-themed-dialog__body {
            padding: 16px 18px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .pos-themed-dialog__text {
            margin: 0 0 4px 0;
            font-size: 0.9rem;
            color: var(--ps-muted);
            line-height: 1.45;
        }

        .pos-themed-label {
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--ps-muted);
        }

        .pos-themed-req {
            color: #f87171;
        }

        .pos-themed-input {
            width: 100%;
            padding: 10px 12px;
            border-radius: 10px;
            border: 1px solid var(--ps-border);
            background: var(--ps-bg) !important;
            color: var(--ps-text) !important;
            font-size: 0.92rem;
        }

        .pos-themed-input:focus {
            outline: none;
            border-color: var(--ps-accent);
            box-shadow: 0 0 0 3px color-mix(in srgb, var(--ps-accent) 35%, transparent);
        }

        .pos-themed-dialog__footer {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            flex-wrap: wrap;
            padding: 14px 18px 16px;
            border-top: 1px solid var(--ps-border);
            background: var(--ps-bg);
        }

        .pos-themed-btn {
            border-radius: 10px;
            font-size: 0.9rem;
            font-weight: 600;
            padding: 9px 16px;
            cursor: pointer;
            border: none;
            transition: background 0.15s ease, transform 0.12s ease;
        }

        .pos-themed-btn--ghost {
            background: transparent;
            color: var(--ps-text);
            border: 1px solid var(--ps-border);
        }

        .pos-themed-btn--ghost:hover {
            background: color-mix(in srgb, var(--ps-text) 6%, transparent);
        }

        .pos-themed-btn--primary {
            background: var(--ps-accent);
            color: var(--ps-navbar-text);
            box-shadow: 0 6px 16px color-mix(in srgb, var(--ps-accent) 45%, transparent);
        }

        .pos-themed-btn--primary:hover {
            background: color-mix(in srgb, var(--ps-accent) 78%, #000000);
        }
    </style>
@endpush

@push('scripts')
    <script src="{{ url('js/pos-custom.js') }}?v={{ time() }}"></script>
    <script src="{{ url('js/pos-products-grid.js') }}?v={{ time() }}"></script>
    <script src="{{ url('js/pos-cart-sidebar.js') }}?v={{ time() }}"></script>
    <script>
        // Initialize POS system setelah DOM ready
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof POSSystem !== 'undefined') {
                window.posSystem = new POSSystem();
            } else {
                console.error('POSSystem class not found. Make sure pos-custom.js is loaded.');
            }

            // Load products grid
            if (typeof ProductsGrid !== 'undefined') {
                window.productsGrid = new ProductsGrid();
                window.productsGrid.loadProducts();
            }
        });

        // Delivery method change
        document.addEventListener('change', function(e) {
            if (e.target.name === 'delivery_method') {
                const deliveryInfo = document.getElementById('delivery-info');
                if (e.target.value === 'delivery') {
                    deliveryInfo.style.display = 'block';
                } else {
                    deliveryInfo.style.display = 'none';
                }
            }
        });

        function goToCheckout() {
            if (window.posSystem) {
                window.posSystem.goToPayment();
            }
        }

        function goBackToProducts() {
            if (window.posSystem) {
                window.posSystem.updateStep(1);
            }
        }

        function updateCartSummary() {
            if (window.posSystem && window.posSystem.updateCartSummary) {
                window.posSystem.updateCartSummary();
            }
        }

        // Update cart summary saat discount berubah
        document.addEventListener('DOMContentLoaded', function() {
            const discountInput = document.getElementById('cart-discount');
            if (discountInput) {
                discountInput.addEventListener('input', updateCartSummary);
                discountInput.addEventListener('change', updateCartSummary);
            }
        });

        function setQuickPayment(percentage) {
            if (window.posSystem) {
                const total = window.posSystem.cart.reduce((sum, item) => sum + (item.price * item.qty), 0);
                const discount = parseFloat(document.getElementById('discount').value) || 0;
                const discountAmount = total * (discount / 100);
                const afterDiscount = total - discountAmount;
                const paymentAmount = Math.floor(afterDiscount * percentage);
                window.posSystem.setAmountInputValue('payment', paymentAmount);
                if (window.posSystem.updatePricing) {
                    window.posSystem.updatePricing();
                }
            }
        }

        function startNewTransaction() {
            if (window.posSystem) {
                window.posSystem.cart = [];
                window.posSystem.renderCart();
                window.posSystem.updateStep(1);
                document.getElementById('checkout-form').reset();
            }
        }

        function closeCreditModal() {
            if (window.posSystem) {
                window.posSystem.closeCreditModal();
            }
        }

        function confirmCredit() {
            if (window.posSystem) {
                window.posSystem.confirmCredit();
            }
        }
    </script>
@endpush
