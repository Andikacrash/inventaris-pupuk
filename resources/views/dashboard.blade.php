@extends('layouts.app')

@section('content')
    @php
        $isAdmin = Auth::user()->isAdmin();
        $isKasir = Auth::user()->isKasir();
        $isManager = Auth::user()->isManager();
        $showSalesAnalysis = $isAdmin || $isManager;
        $debtDashLink = $isAdmin || $isKasir;
    @endphp
    <div class="dash-page" data-initial-month="{{ now()->format('Y-m') }}">
        <div class="dash-header">
            <div>
                <h2 class="dash-title">Ringkasan Hari Ini, {{ Auth::user()->name }}</h2>
                <p class="dash-subtitle">Catatan singkat penjualan & stok hari ini</p>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-6 col-lg-3">
                <div class="dash-stat-card dash-stat-card--hero dash-stat-card--sales">
                    <div class="dash-stat-top">
                        <div class="dash-stat-label" id="period-sales-label">
                            {{ $isKasir ? 'Penjualan hari ini' : 'Total penjualan' }}</div>
                        <div class="dash-stat-ico" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none">
                                <path d="M7 7h10v10H7V7Z" stroke="currentColor" strokeWidth="2" />
                                <path d="M10 12h4" stroke="currentColor" strokeWidth="2" strokeLinecap="round" />
                            </svg>
                        </div>
                    </div>
                    <div class="dash-stat-value" id="period-sales-value">Rp 0</div>
                    <div class="dash-stat-note" id="period-sales-note">
                        {{ $isKasir ? 'Akumulasi nota hari ini' : 'Periode yang dipilih' }}</div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="dash-stat-card dash-stat-card--hero dash-stat-card--days">
                    <div class="dash-stat-top">
                        <div class="dash-stat-label" id="period-days-label">
                            {{ $isKasir ? 'Transaksi hari ini' : 'Total hari penjualan aktif' }}</div>
                        <div class="dash-stat-ico" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none">
                                <path d="M7 3v3M17 3v3" stroke="currentColor" strokeWidth="2" strokeLinecap="round" />
                                <path d="M4 8h16" stroke="currentColor" strokeWidth="2" strokeLinecap="round" />
                                <path d="M6 6h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2Z"
                                    stroke="currentColor" strokeWidth="2" />
                            </svg>
                        </div>
                    </div>
                    <div class="dash-stat-value" id="period-sales-count">0</div>
                    <div class="dash-stat-note" id="period-days-note">
                        {{ $isKasir ? 'Jumlah nota terjual' : 'Hari dengan penjualan > 0' }}</div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                @if ($isAdmin || $isManager)
                    <a href="/products?filter=low_stock" class="dash-stat-link">
                        <div class="dash-stat-card dash-stat-card--hero dash-stat-warning dash-stat-card--stock">
                            <div class="dash-stat-top">
                                <div class="dash-stat-label">Total Barang Menipis</div>
                                <div class="dash-stat-ico" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="none">
                                        <path d="M12 9v4" stroke="currentColor" strokeWidth="2" strokeLinecap="round" />
                                        <path d="M12 17h.01" stroke="currentColor" strokeWidth="3" strokeLinecap="round" />
                                        <path
                                            d="M10.3 4.3 2.6 18a2 2 0 0 0 1.7 3h15.4a2 2 0 0 0 1.7-3L13.7 4.3a2 2 0 0 0-3.4 0Z"
                                            stroke="currentColor" strokeWidth="2" strokeLinejoin="round" />
                                    </svg>
                                </div>
                            </div>
                            <div class="dash-stat-value" id="low-stock-count">0 Produk</div>
                            <div class="dash-stat-note">Perlu restok segera</div>
                        </div>
                    </a>
                @else
                    <div class="dash-stat-card dash-stat-card--hero dash-stat-warning dash-stat-card--stock">
                        <div class="dash-stat-top">
                            <div class="dash-stat-label">Total Barang Menipis</div>
                            <div class="dash-stat-ico" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none">
                                    <path d="M12 9v4" stroke="currentColor" strokeWidth="2" strokeLinecap="round" />
                                    <path d="M12 17h.01" stroke="currentColor" strokeWidth="3" strokeLinecap="round" />
                                    <path
                                        d="M10.3 4.3 2.6 18a2 2 0 0 0 1.7 3h15.4a2 2 0 0 0 1.7-3L13.7 4.3a2 2 0 0 0-3.4 0Z"
                                        stroke="currentColor" strokeWidth="2" strokeLinejoin="round" />
                                </svg>
                            </div>
                        </div>
                        <div class="dash-stat-value" id="low-stock-count">0 Produk</div>
                        <div class="dash-stat-note">Info stok (kelola oleh admin)</div>
                    </div>
                @endif
            </div>
            <div class="col-6 col-lg-3">
                @if ($debtDashLink)
                    <a href="/debts?status=unpaid" class="dash-stat-link">
                        <div class="dash-stat-card dash-stat-card--hero dash-stat-card--debt">
                            <div class="dash-stat-top">
                                <div class="dash-stat-label">Hutang pelanggan aktif</div>
                                <div class="dash-stat-ico" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="none">
                                        <path d="M4 7h16v10H4V7Z" stroke="currentColor" strokeWidth="2" />
                                        <path d="M8 11h8" stroke="currentColor" strokeWidth="2" strokeLinecap="round" />
                                        <path d="M8 14h5" stroke="currentColor" strokeWidth="2" strokeLinecap="round"
                                            opacity="0.7" />
                                    </svg>
                                </div>
                            </div>
                            <div class="dash-stat-value" id="active-receivables">Rp 0</div>
                            <div class="dash-stat-note">Total sisa hutang pelanggan</div>
                        </div>
                    </a>
                @else
                    <div class="dash-stat-card dash-stat-card--hero dash-stat-card--debt">
                        <div class="dash-stat-top">
                            <div class="dash-stat-label">Hutang pelanggan aktif</div>
                            <div class="dash-stat-ico" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none">
                                    <path d="M4 7h16v10H4V7Z" stroke="currentColor" strokeWidth="2" />
                                    <path d="M8 11h8" stroke="currentColor" strokeWidth="2" strokeLinecap="round" />
                                    <path d="M8 14h5" stroke="currentColor" strokeWidth="2" strokeLinecap="round"
                                        opacity="0.7" />
                                </svg>
                            </div>
                        </div>
                        <div class="dash-stat-value" id="active-receivables">Rp 0</div>
                        <div class="dash-stat-note">Total sisa hutang pelanggan</div>
                    </div>
                @endif
            </div>
        </div>

        <div class="row g-3">
            <div class="col-xl-7">
                @if ($showSalesAnalysis)
                    <div class="dash-panel p-3 p-md-4">
                        <div id="sales-analysis-root" data-initial-month="{{ now()->format('Y-m') }}"></div>
                    </div>
                @endif

                @if ($isAdmin || $isKasir)
                    <div class="dash-panel p-3 p-md-4 {{ $isAdmin ? 'mt-3' : '' }}">
                        <h3 class="h6 m-0 fw-semibold mb-2">Ringkasan hutang pelanggan</h3>
                        <div class="dash-debt-alert mb-3" id="debt-alert-box">Memuat data hutang...</div>
                        <div id="debt-management-list" class="dash-list"></div>
                    </div>
                @endif

                @if ($isKasir)
                    <div class="dash-panel p-3 p-md-4 mt-3">
                        <h3 class="h6 m-0 fw-semibold mb-3">Aktivitas Terbaru</h3>
                        <div id="recent-sales-list" class="dash-list"></div>
                    </div>
                @endif
            </div>
            <div class="col-xl-5">
                @if (!$isKasir)
                    <div class="dash-panel p-3 p-md-4">
                        <h3 class="h6 m-0 fw-semibold mb-3">Aktivitas Terbaru</h3>
                        <div id="recent-sales-list" class="dash-list"></div>
                    </div>
                @endif
                <div class="dash-panel dash-stock-panel p-3 p-md-4">
                    <h3 class="h6 m-0 fw-semibold mb-3">Peringatan Stok</h3>
                    <div id="low-stock-list" class="dash-list"></div>
                </div>
                <div class="dash-panel p-3 p-md-4 mt-3">
                    <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
                        <h3 class="h6 m-0 fw-semibold">Top Pupuk Terjual</h3>
                        <select id="top-products-period" class="form-select form-select-sm" style="max-width: 200px;">
                            <option value="month_selected">Bulan dipilih</option>
                            <option value="last_3_months">3 bulan terakhir</option>
                            <option value="all_time">Semua waktu</option>
                        </select>
                    </div>
                    <div id="top-products-bars" class="dash-top-products"></div>
                </div>
                <div class="dash-panel p-3 p-md-4 mt-3">
                    <div class="row g-2">
                        <div class="col-12">
                            <a href="/sales" class="btn btn-primary w-100"><i class="fas fa-plus-circle me-2"></i>Tambah
                                Transaksi</a>
                        </div>
                        @if ($isAdmin)
                            <div class="col-12">
                                <a href="/products" class="btn btn-outline-primary w-100"><i
                                        class="fas fa-boxes-stacked me-2"></i>Tambah Stok</a>
                            </div>
                        @endif
                        @if ($isAdmin || $isKasir)
                            <div class="col-12">
                                <a href="/debts" class="btn btn-outline-primary w-100"><i
                                        class="fas fa-money-check-dollar me-2"></i>Input Pembayaran Hutang</a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const dashCanManageStock = @json($isAdmin || $isManager);

        function toRupiah(value) {
            const num = Number(value || 0);
            return 'Rp ' + num.toLocaleString('id-ID');
        }

        function parseNumberString(value) {
            if (value === null || value === undefined) return 0;
            return Number(String(value).replaceAll(',', '')) || 0;
        }

        function shortTime(datetime) {
            const d = new Date(datetime);
            if (Number.isNaN(d.getTime())) return '-';
            return d.toLocaleTimeString('id-ID', {
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        function dashboardInitialMonth() {
            return document.querySelector('.dash-page')?.dataset.initialMonth || '';
        }

        async function loadSummary() {
            const res = await fetch('/api/dashboard/summary');
            const summary = await res.json();
            const kasirMode = !document.getElementById('sales-analysis-root');

            document.getElementById('low-stock-count').textContent = `${summary.low_stock_products || 0} Produk`;
            const recEl = document.getElementById('active-receivables');
            if (recEl) {
                recEl.textContent = toRupiah(parseNumberString(summary.active_receivables));
            }

            if (kasirMode) {
                const salesVal = document.getElementById('period-sales-value');
                const salesNote = document.getElementById('period-sales-note');
                const cntEl = document.getElementById('period-sales-count');
                if (salesVal) salesVal.textContent = toRupiah(parseNumberString(summary.today_sales));
                if (cntEl) cntEl.textContent = String(summary.today_sales_count || 0);
                if (salesNote) {
                    salesNote.textContent = 'Akumulasi nota hari ini';
                    salesNote.style.color = '';
                }
            }
        }

        function getTopProductsPeriod() {
            return document.getElementById('top-products-period')?.value || 'month_selected';
        }

        async function loadTopProductsChart(month) {
            const m = month || document.getElementById('sales-analysis-root')?.dataset.initialMonth || dashboardInitialMonth();
            const periodMode = getTopProductsPeriod();
            const params = new URLSearchParams({
                limit: '5',
            });

            if (periodMode === 'month_selected') {
                params.set('month', m);
            } else if (periodMode === 'last_3_months') {
                params.set('period', 'last_3_months');
            } else if (periodMode === 'all_time') {
                params.set('period', 'all_time');
            }

            const res = await fetch(`/api/dashboard/top-products?${params.toString()}`);
            const products = await res.json();
            const list = document.getElementById('top-products-bars');
            if (!list) return;

            if (!products.length) {
                list.innerHTML = '<div class="dash-list-empty">Belum ada data penjualan produk.</div>';
                return;
            }

            const max = Math.max(...products.map((item) => Number(item.total_qty || 0)), 1);
            list.innerHTML = products.map((item, idx) => {
                const qty = Number(item.total_qty || 0);
                const percent = Math.round((qty / max) * 100);
                const barClass = idx === 0 ? 'is-primary' : 'is-secondary';
                return `
                    <div class="dash-top-product-item">
                        <div class="dash-top-product-head">
                            <span class="dash-top-product-name">${item.name}</span>
                            <span class="dash-top-product-percent">${qty.toLocaleString('id-ID')} unit</span>
                        </div>
                        <div class="dash-top-product-track">
                            <span class="dash-top-product-fill ${barClass}" style="width:${percent}%"></span>
                        </div>
                    </div>
                `;
            }).join('');

        }

        async function loadDebtManagementSummary() {
            const list = document.getElementById('debt-management-list');
            const alert = document.getElementById('debt-alert-box');
            if (!list || !alert) return;

            const res = await fetch('/api/dashboard/debt-management');
            const data = await res.json();
            alert.textContent =
                `${data.overdue_count || 0} pelanggan jatuh tempo (Total ${toRupiah(data.overdue_amount || 0)})`;

            if (!data.items || !data.items.length) {
                list.innerHTML = '<div class="dash-list-empty">Tidak ada data hutang aktif.</div>';
                return;
            }

            list.innerHTML = data.items.map((item) => `
                <div class="dash-list-item">
                    <div>
                        <div class="dash-list-title">${item.customer_name}</div>
                        <div class="dash-list-subtitle">${toRupiah(item.remaining_amount)}${item.due_date_formatted ? ` · Jatuh tempo ${item.due_date_formatted}` : ''}</div>
                    </div>
                    <div class="dash-list-value">${item.due_label}</div>
                </div>
            `).join('');
        }

        async function loadLowStockAlerts() {
            const res = await fetch('/api/dashboard/low-stock-alerts');
            const items = await res.json();
            const list = document.getElementById('low-stock-list');

            if (!items.length) {
                list.innerHTML = '<div class="dash-list-empty">Tidak ada produk dengan stok menipis.</div>';
                return;
            }

            list.innerHTML = items.slice(0, 4).map((item) => {
                const qtyCls = Number(item.stock_quantity) === 0 ? 'danger' : 'warning';
                const inner = `
                    <div>
                        <div class="dash-list-title">${item.name}</div>
                        <div class="dash-list-subtitle">${item.category?.name || 'Tanpa kategori'}</div>
                    </div>
                    <div class="dash-list-value ${qtyCls}">${item.stock_quantity} / ${item.minimum_stock}</div>`;
                if (dashCanManageStock) {
                    return `<a href="/products?search=${encodeURIComponent(item.name)}" class="dash-list-item dash-list-link">${inner}</a>`;
                }
                return `<div class="dash-list-item">${inner}</div>`;
            }).join('');
        }

        async function loadRecentSales() {
            const res = await fetch('/api/dashboard/recent-sales');
            const items = await res.json();
            const list = document.getElementById('recent-sales-list');

            if (!items.length) {
                list.innerHTML = '<div class="dash-list-empty">Belum ada aktivitas transaksi terbaru.</div>';
                return;
            }

            list.innerHTML = items.slice(0, 5).map((item) => `
                <div class="dash-list-item">
                    <div>
                        <div class="dash-list-title">${item.title || `Penjualan #TRX${String(item.id).padStart(4, '0')}`}</div>
                        <div class="dash-list-subtitle">${item.served_by || 'Petugas'} • ${item.time_label || '-'}${item.qty_total ? ` • ${item.qty_total} pcs` : ''}</div>
                    </div>
                    <div class="dash-list-value success">${toRupiah(item.total_amount)}</div>
                </div>
            `).join('');
        }

        async function initializeDashboard() {
            await Promise.all([
                loadSummary(),
                loadLowStockAlerts(),
                loadRecentSales(),
                loadDebtManagementSummary()
            ]);
            await loadTopProductsChart(
                document.getElementById('sales-analysis-root')?.dataset.initialMonth || dashboardInitialMonth()
            );
        }

        document.addEventListener('DOMContentLoaded', async () => {
            try {
                const periodSelect = document.getElementById('top-products-period');
                if (periodSelect) {
                    periodSelect.addEventListener('change', () => {
                        loadTopProductsChart(
                            document.getElementById('sales-analysis-root')?.dataset.initialMonth || dashboardInitialMonth()
                        );
                    });
                }
                await initializeDashboard();
            } catch (err) {
                console.error('Gagal memuat dashboard:', err);
            }
        });

        // Event dari React SalesAnalysis untuk sinkronisasi komponen lain di dashboard
        window.addEventListener('sa:monthChange', (ev) => {
            const month = ev?.detail?.month;
            if (month) loadTopProductsChart(month);
        });

        window.addEventListener('sa:summary', (ev) => {
            const d = ev?.detail || {};
            const label = d.monthLabel || '';
            if (document.getElementById('period-sales-label')) {
                document.getElementById('period-sales-label').textContent = label ? `Total penjualan ${label}` :
                    'Total penjualan';
            }
            if (document.getElementById('period-sales-value')) {
                document.getElementById('period-sales-value').textContent = toRupiah(d.totalSales || 0);
            }
            if (document.getElementById('period-sales-count')) {
                const days = Number(d.activeDays) || 0;
                document.getElementById('period-sales-count').textContent = `${days} hari`;
            }
            if (document.getElementById('period-sales-note')) {
                document.getElementById('period-sales-note').textContent = d.mode === 'single' ?
                    'Total penjualan bulan ini' :
                    'Grafik perbandingan 2 bulan';
                document.getElementById('period-sales-note').style.color = '';
            }
        });
    </script>
@endpush
