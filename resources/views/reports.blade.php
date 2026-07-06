@extends('layouts.app')

@push('styles')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .sales-report-page { font-family: var(--wn-font, 'Segoe UI', system-ui, sans-serif); color: #1e1710; font-size: 16px; line-height: 1.65; font-weight: 500; }
        .sales-shell { background: #ede8e1; border: 1px solid #b8aea3; border-radius: 12px; padding: 20px 24px; }
        .sales-header { display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; margin-bottom: 20px; flex-wrap: wrap; }
        .sales-title { margin: 0; font-size: 1.5rem; font-weight: 600; color: #1e1710; letter-spacing: -0.01em; }
        .sales-subtitle { margin: 8px 0 0; color: #3d2e22; font-size: 16px; max-width: 520px; line-height: 1.6; font-weight: 500; }
        .sales-download-wrap { position: relative; min-width: 220px; }
        .sales-download-toggle {
            border: 1.5px solid #1a5c42;
            background: #1a5c42;
            color: #f8faf8;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 600;
            padding: 10px 16px;
            min-height: 44px;
            width: 100%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            cursor: pointer;
        }
        .sales-download-toggle:hover, .sales-download-toggle:focus {
            background: #256b4f;
            border-color: #256b4f;
            color: #f8faf8;
        }
        .sales-download-toggle:disabled { opacity: 0.75; cursor: wait; }
        .sales-download-toggle--sm {
            font-size: 14px;
            padding: 8px 14px;
            min-height: 44px;
            background: #f4f1ec;
            color: #1a5c42;
            border: 2px solid #1a5c42;
        }
        .sales-download-toggle--sm:hover,
        .sales-download-toggle--sm:focus {
            background: #d4ebe3;
            color: #144a35;
            border-color: #144a35;
        }
        .sales-download-chevron { font-size: 12px; transition: transform 0.2s ease; }
        .sales-download-wrap.is-open .sales-download-chevron { transform: rotate(180deg); }
        .sales-download-menu {
            display: none;
            position: absolute;
            top: calc(100% + 8px);
            right: 0;
            left: 0;
            z-index: 1050;
            background: #f4f1ec;
            border: 0.5px solid #c8bfb4;
            border-radius: 12px;
            padding: 8px;
            box-shadow: 0 8px 24px rgba(30, 23, 16, 0.12);
            flex-direction: column;
            gap: 6px;
        }
        .sales-download-wrap.is-open .sales-download-menu { display: flex; }
        .sales-download-menu-item {
            border: 1px solid #c8bfb4;
            background: #ede8e1;
            color: #1e1710;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            padding: 10px 12px;
            min-height: 40px;
            width: 100%;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            text-align: left;
        }
        .sales-download-menu-item:hover, .sales-download-menu-item:focus {
            background: rgba(26, 92, 66, 0.12);
            border-color: #1a5c42;
            color: #1a5c42;
        }
        .sales-download-menu-item:disabled { opacity: 0.6; cursor: wait; }
        .sales-download-menu-item i { color: #1a5c42; width: 18px; }
        .sales-download-status {
            font-size: 13px;
            color: #4a3728;
            margin-top: 8px;
            min-height: 20px;
            line-height: 1.4;
        }
        .sales-download-status.is-loading { color: #1a5c42; font-weight: 600; }
        .sales-download-status.is-success { color: #1a5c42; font-weight: 600; }
        .sales-download-status.is-error { color: #a32d2d; font-weight: 600; }
        .sales-filter-card { background: #ede8e1; border: 0.5px solid #c8bfb4; border-radius: 12px; padding: 20px; margin-bottom: 20px; position: sticky; top: 76px; z-index: 20; }
        .sales-filter-title { margin: 0; font-size: 1.1rem; font-weight: 600; color: #1e1710; }
        .sales-filter-guide { margin: 8px 0 16px; font-size: 15px; color: #3d2e22; line-height: 1.6; font-weight: 500; }
        .sales-filter-label { font-size: 14px; font-weight: 600; color: #1e1710; margin-bottom: 8px; }
        .sales-filter-actions { margin-top: 16px; display: flex; gap: 12px; justify-content: flex-end; flex-wrap: wrap; }
        .sales-btn-primary { border: 0.5px solid #1a5c42; background: #1a5c42; color: #f8faf8; border-radius: 12px; font-size: 16px; padding: 12px 20px; font-weight: 500; min-height: 48px; }
        .sales-btn-outline { border: 0.5px solid #c8bfb4; background: #f4f1ec; color: #1e1710; border-radius: 12px; font-size: 16px; padding: 12px 20px; font-weight: 500; min-height: 48px; }
        .sales-btn-primary:hover, .sales-btn-primary:focus { background: #256b4f; border-color: #256b4f; color: #f8faf8; }
        .sales-kpi-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; margin-bottom: 16px; }
        .sales-kpi { background: #ede8e1; border: 0.5px solid #c8bfb4; border-radius: 12px; padding: 16px; border-left: 4px solid #4a3728; }
        .sales-kpi.blue { border-left-color: #1a5c42; }
        .sales-kpi.green { border-left-color: #1a5c42; }
        .sales-kpi.amber { border-left-color: #ba7517; }
        .sales-kpi.red { border-left-color: #a32d2d; }
        .sales-kpi-top { display: flex; justify-content: space-between; align-items: flex-start; gap: 10px; margin-bottom: 10px; }
        .sales-kpi-label { font-size: 14px; color: #1e1710; font-weight: 600; }
        .sales-kpi-ico { width: 40px; height: 40px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; background: #d4ebe3; color: #1a5c42; font-size: 1rem; border: 1.5px solid #1a5c42; }
        .sales-kpi-value { font-size: 1.35rem; font-weight: 700; color: #1e1710; line-height: 1.25; }
        .sales-kpi-desc { margin-top: 6px; font-size: 14px; color: #3d2e22; line-height: 1.5; font-weight: 500; }
        .sales-kpi-tag { margin-top: 8px; display: inline-block; font-size: 13px; font-weight: 600; padding: 4px 12px; border-radius: 8px; border: 1.5px solid #b8aea3; color: #1e1710; background: #f4f1ec; }
        .sales-empty { display: none; border: 0.5px solid rgba(186, 117, 23, 0.45); background: rgba(186, 117, 23, 0.1); color: #4a3728; border-radius: 12px; padding: 14px 16px; font-size: 16px; line-height: 1.55; margin-bottom: 16px; }
        .sales-empty.is-visible { display: block; }
        .sales-chart-card { background: #f4f1ec; border: 1px solid #b8aea3; border-radius: 12px; padding: 16px; margin-bottom: 16px; }
        .sales-chart-title { margin: 0; font-size: 1.05rem; font-weight: 600; color: #1e1710; }
        .sales-chart-hint { margin: 8px 0 14px; font-size: 15px; color: #3d2e22; line-height: 1.5; font-weight: 500; }
        .sales-chart-wrap { position: relative; min-height: 280px; max-height: 320px; }
        .sales-panels { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; margin-bottom: 16px; }
        .sales-panel { background: #ede8e1; border: 0.5px solid #c8bfb4; border-radius: 12px; padding: 16px; min-height: 150px; }
        .sales-panel h3 { margin: 0 0 12px; font-size: 1.05rem; font-weight: 600; color: #1e1710; }
        .sales-top-item { margin-bottom: 14px; }
        .sales-top-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 10px; font-size: 15px; color: #1e1710; margin-bottom: 6px; font-weight: 600; }
        .sales-top-name { color: #1e1710; font-weight: 600; line-height: 1.4; flex: 1; min-width: 0; word-break: break-word; }
        .sales-top-qty { flex-shrink: 0; color: #1a5c42; font-weight: 700; }
        .sales-top-bar { height: 10px; border-radius: 999px; background: #e0d8cf; overflow: hidden; border: 1px solid #c8bfb4; }
        .sales-top-fill { height: 100%; border-radius: 999px; background: #1a5c42; display: block; }
        .sales-muted-msg { margin: 0; font-size: 15px; color: #3d2e22; font-weight: 500; }
        .sales-pay-row { display: flex; align-items: flex-start; gap: 12px; padding: 10px 0; border-bottom: 0.5px solid #c8bfb4; }
        .sales-pay-row:last-child { border-bottom: 0; }
        .sales-pay-dot { width: 10px; height: 10px; border-radius: 50%; margin-top: 6px; flex-shrink: 0; }
        .sales-pay-name { font-size: 16px; font-weight: 600; color: #1e1710; }
        .sales-pay-meta { font-size: 14px; color: #3d2e22; margin-top: 4px; font-weight: 500; }
        .sales-table-card { background: #ede8e1; border: 1px solid #b8aea3; border-radius: 12px; overflow: hidden; }
        .sales-table-card .card-header { background: #f4f1ec !important; border-bottom: 1px solid #b8aea3 !important; color: #1e1710; font-weight: 600; font-size: 16px; }
        .sales-table thead th { background: #f4f1ec; color: #1e1710; font-size: 14px; font-weight: 600; border-color: #b8aea3; border-bottom-width: 1px; padding: 12px 14px; }
        .sales-table tbody td { background: #ede8e1; color: #1e1710; border-color: #c8bfb4; font-size: 15px; font-weight: 500; padding: 12px 14px; vertical-align: middle; }
        .sales-table tbody td.sales-td-money { font-weight: 600; font-variant-numeric: lining-nums; font-feature-settings: normal; }
        .sales-table tbody tr:hover td { background: #f4f1ec; }
        .sales-act-group {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            align-items: center;
        }
        .sales-table .sales-act-btn {
            display: inline-flex !important;
            align-items: center;
            justify-content: center;
            gap: 6px;
            border-radius: 10px !important;
            font-size: 14px !important;
            font-weight: 700 !important;
            padding: 8px 14px !important;
            min-height: 42px !important;
            min-width: 72px;
            border-style: solid !important;
            border-width: 2px !important;
            box-shadow: 0 1px 3px rgba(30, 23, 16, 0.08);
            text-decoration: none !important;
            line-height: 1.2;
        }
        .sales-act-btn--view {
            border-color: #1a5c42 !important;
            color: #1a5c42 !important;
            background: #f4f1ec !important;
        }
        .sales-act-btn--view:hover {
            background: #d4ebe3 !important;
            color: #144a35 !important;
            border-color: #144a35 !important;
        }
        .sales-act-btn--edit {
            border-color: #6b5c4e !important;
            color: #1e1710 !important;
            background: #f4f1ec !important;
        }
        .sales-act-btn--edit:hover {
            background: #ebe6e0 !important;
            border-color: #1e1710 !important;
        }
        .sales-act-btn--del {
            border-color: #a32d2d !important;
            color: #a32d2d !important;
            background: #f4f1ec !important;
        }
        .sales-act-btn--del:hover {
            background: rgba(163, 45, 45, 0.12) !important;
            color: #8a2525 !important;
            border-color: #8a2525 !important;
        }
        .sales-table-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            padding: 14px 16px !important;
        }
        .sales-table-card-header .sales-download-wrap {
            min-width: 200px;
            flex: 0 1 auto;
        }
        .sales-btn-excel-quick {
            border: 2px solid #1a5c42;
            background: #1a5c42;
            color: #f8faf8;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 700;
            padding: 10px 18px;
            min-height: 44px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }
        .sales-btn-excel-quick:hover,
        .sales-btn-excel-quick:focus {
            background: #256b4f;
            border-color: #256b4f;
            color: #f8faf8;
        }
        .sales-btn-excel-quick:disabled {
            opacity: 0.75;
            cursor: wait;
        }
        .sales-table-footer { display:flex; align-items:center; justify-content:space-between; gap:12px; padding:14px 16px; border-top:0.5px solid #c8bfb4; background:#ede8e1; }
        .sales-table-meta { font-size:15px; color: #3d2e22; font-weight: 500; }
        .sales-table-meta strong { color: #1e1710; font-weight: 600; }
        .sales-pagination .pagination { margin:0; gap:8px; }
        .sales-pagination .page-link { background:#f4f1ec; border:0.5px solid #c8bfb4; color:#1e1710; border-radius:12px; min-width:44px; height:44px; display:inline-flex; align-items:center; justify-content:center; font-size:15px; }
        .sales-pagination .page-link:hover { background:rgba(46,125,94,0.08); border-color:#1a5c42; color:#1a5c42; }
        .sales-pagination .page-item.active .page-link { background:rgba(46,125,94,0.15); border-color:#1a5c42; color:#1a5c42; font-weight:600; }
        .sales-pagination .page-item.disabled .page-link { opacity:0.45; }
        .sales-page .form-control, .sales-page .form-select { background: #f4f1ec; border: 0.5px solid #c8bfb4; color: #1e1710; min-height: 44px; border-radius: 12px; font-size: 16px; padding: 10px 14px; }
        .sales-page .form-control:focus, .sales-page .form-select:focus { border-color: #1a5c42; box-shadow: 0 0 0 3px rgba(26, 92, 66, 0.18); background: #f4f1ec; color: #1e1710; }
        .sales-page .form-check-label { color: #1e1710; font-size: 15px; font-weight: 500; }
        .sales-search-row { background:#ede8e1; border:0.5px solid #c8bfb4; border-radius:12px; padding:16px; margin-bottom:16px; }
        .sales-search-title { margin:0 0 12px; font-size:1rem; font-weight:500; color:#1e1710; }
        @media (max-width: 991.98px) {
            .sales-kpi-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .sales-panels { grid-template-columns: 1fr; }
        }
        @media (max-width: 575.98px) { .sales-kpi-grid { grid-template-columns: 1fr; } }
    </style>
@endpush

@section('content')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        $namaBulan = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'];
    @endphp
    <div class="sales-report-page sales-page">
        <div class="sales-shell">
            <div class="sales-header">
                <div>
                    <h1 class="sales-title">Laporan Penjualan Toko</h1>
                    <p class="sales-subtitle">Ringkasan penjualan dalam bahasa sederhana. Pilih tanggal lalu tekan tombol untuk melihat angka dan grafik.</p>
                </div>
                <div class="sales-download-wrap" id="sales-download-wrap">
                    <button type="button" class="sales-download-toggle" id="btn-sales-download-toggle" aria-expanded="false" aria-haspopup="true">
                        <i class="fas fa-download"></i>
                        <span>Unduh Laporan</span>
                        <i class="fas fa-chevron-down sales-download-chevron" aria-hidden="true"></i>
                    </button>
                    <div class="sales-download-menu" id="sales-download-menu" role="menu">
                        <button type="button" class="sales-download-menu-item" data-sales-period="daily" role="menuitem">
                            <i class="fas fa-file-excel"></i> Laporan Harian
                        </button>
                        <button type="button" class="sales-download-menu-item" data-sales-period="weekly" role="menuitem">
                            <i class="fas fa-file-excel"></i> Laporan Mingguan
                        </button>
                        <button type="button" class="sales-download-menu-item" data-sales-period="monthly" role="menuitem">
                            <i class="fas fa-file-excel"></i> Laporan Bulanan
                        </button>
                        <button type="button" class="sales-download-menu-item" data-sales-period="filter" role="menuitem">
                            <i class="fas fa-file-excel"></i> Sesuai Filter Tanggal
                        </button>
                    </div>
                    <p class="sales-download-status mb-0" id="sales-download-status" aria-live="polite">
                        Pilih rentang tanggal di filter, lalu unduh &quot;Sesuai Filter Tanggal&quot;.
                    </p>
                </div>
            </div>

            <div class="sales-filter-card">
                <h2 class="sales-filter-title">Tampilkan Laporan</h2>
                <p class="sales-filter-guide">Pilih tanggal atau ketik nama, lalu tekan <strong>Tampilkan</strong>. Bisa juga langsung ketik—data akan ikut mencari otomatis.</p>
                <div class="row g-2 g-md-3">
                    <div class="col-6 col-md-3">
                        <label for="filter-month" class="sales-filter-label d-block">Bulan</label>
                        <select class="form-select" id="filter-month">
                            @for ($i = 0; $i < 18; $i++)
                                @php $d = now()->copy()->subMonths($i); @endphp
                                <option value="{{ $d->format('Y-m') }}" @if ($i === 0) selected @endif>
                                    {{ $namaBulan[$d->month] }} {{ $d->year }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-6 col-md-3">
                        <label for="start-date" class="sales-filter-label d-block">Dari Tanggal</label>
                        <input type="date" class="form-control" id="start-date" value="{{ now()->startOfMonth()->format('Y-m-d') }}">
                    </div>
                    <div class="col-6 col-md-3">
                        <label for="end-date" class="sales-filter-label d-block">Sampai Tanggal</label>
                        <input type="date" class="form-control" id="end-date" value="{{ now()->format('Y-m-d') }}">
                    </div>
                    <!-- Pencarian dipindah ke bawah dekat tabel -->
                    <div class="col-md-6 d-flex align-items-end">
                        <div class="form-check mb-1">
                            <input class="form-check-input" type="checkbox" id="show-cancelled">
                            <label class="form-check-label" for="show-cancelled">Tampilkan transaksi yang dibatalkan</label>
                        </div>
                    </div>
                </div>
                <div class="sales-filter-actions">
                    <button type="button" class="sales-btn-outline" id="btn-reset-filters">Bersihkan</button>
                    <button type="button" class="sales-btn-primary" id="btn-apply-filters">Tampilkan</button>
                </div>
            </div>

            <div class="sales-empty" id="report-empty-state">
                Data bulan ini belum ada — coba pilih bulan lain atau periksa apakah sudah ada transaksi.
            </div>

            <div class="sales-kpi-grid">
                <div class="sales-kpi blue">
                    <div class="sales-kpi-top">
                        <span class="sales-kpi-label">Total Transaksi</span>
                        <span class="sales-kpi-ico" aria-hidden="true"><i class="fas fa-receipt"></i></span>
                    </div>
                    <div class="sales-kpi-value" id="kpi-total-tx">0</div>
                    <p class="sales-kpi-desc">Berapa kali ada penjualan tercatat di tanggal yang dipilih.</p>
                    <span class="sales-kpi-tag" id="kpi-total-tx-tag">Jumlah struk / transaksi</span>
                </div>
                <div class="sales-kpi green">
                    <div class="sales-kpi-top">
                        <span class="sales-kpi-label">Uang Masuk</span>
                        <span class="sales-kpi-ico" aria-hidden="true"><i class="fas fa-money-bill-wave"></i></span>
                    </div>
                    <div class="sales-kpi-value" id="kpi-cash">Rp 0</div>
                    <p class="sales-kpi-desc">Penjualan yang sudah dibayar langsung (bukan piutang).</p>
                    <span class="sales-kpi-tag">dibayar langsung</span>
                </div>
                <div class="sales-kpi amber">
                    <div class="sales-kpi-top">
                        <span class="sales-kpi-label">Piutang</span>
                        <span class="sales-kpi-ico" aria-hidden="true"><i class="fas fa-hand-holding-dollar"></i></span>
                    </div>
                    <div class="sales-kpi-value" id="kpi-credit">Rp 0</div>
                    <p class="sales-kpi-desc">Total nilai penjualan yang belum lunas dan perlu ditagih.</p>
                    <span class="sales-kpi-tag">masih perlu ditagih</span>
                </div>
                <div class="sales-kpi red">
                    <div class="sales-kpi-top">
                        <span class="sales-kpi-label">Transaksi Dibatalkan</span>
                        <span class="sales-kpi-ico" aria-hidden="true"><i class="fas fa-ban"></i></span>
                    </div>
                    <div class="sales-kpi-value" id="kpi-cancelled">0</div>
                    <p class="sales-kpi-desc">Transaksi yang tidak jadi dan tidak dihitung sebagai penjualan aktif.</p>
                    <span class="sales-kpi-tag">tidak jadi dibayar</span>
                </div>
            </div>

            <div class="sales-chart-card">
                <h2 class="sales-chart-title">Grafik batang harian</h2>
                <p class="sales-chart-hint">Setiap batang = total uang masuk dalam satu hari (dari penjualan yang dihitung di filter di atas).</p>
                <div class="sales-chart-wrap">
                    <canvas id="dailySalesChart" height="120"></canvas>
                </div>
            </div>

            <div class="sales-panels">
                <div class="sales-panel">
                    <h3>Barang Paling Laris</h3>
                    <div id="top-products-list">Memuat…</div>
                </div>
                <div class="sales-panel">
                    <h3>Cara Pelanggan Membayar</h3>
                    <div id="payment-methods-list">Memuat…</div>
                </div>
            </div>

            <div class="sales-search-row">
                <h2 class="sales-search-title">Cari Transaksi</h2>
                <div class="row g-2 g-md-3">
                    <div class="col-12 col-md-6">
                        <label for="search-product" class="sales-filter-label d-block">Nama barang</label>
                        <input type="text" class="form-control" id="search-product" placeholder="Contoh: Urea">
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="search-customer" class="sales-filter-label d-block">Nama pembeli</label>
                        <input type="text" class="form-control" id="search-customer" placeholder="Contoh: Pak Budi">
                    </div>
                </div>
            </div>

            <div class="card sales-table-card border-0 shadow-none mb-3">
                <div class="card-header sales-table-card-header">
                    <span>Daftar Transaksi</span>
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <button type="button" class="sales-btn-excel-quick" id="btn-sales-download-quick" title="Unduh Excel sesuai filter tanggal">
                            <i class="fas fa-file-excel" aria-hidden="true"></i>
                            <span>Unduh Excel</span>
                        </button>
                        <div class="sales-download-wrap" id="sales-download-wrap-table">
                            <button type="button" class="sales-download-toggle sales-download-toggle--sm" aria-expanded="false" aria-haspopup="true">
                                <i class="fas fa-download" aria-hidden="true"></i>
                                <span>Periode lain</span>
                                <i class="fas fa-chevron-down sales-download-chevron" aria-hidden="true"></i>
                            </button>
                            <div class="sales-download-menu" role="menu">
                                <button type="button" class="sales-download-menu-item" data-sales-period="daily" role="menuitem">
                                    <i class="fas fa-file-excel"></i> Laporan Harian
                                </button>
                                <button type="button" class="sales-download-menu-item" data-sales-period="weekly" role="menuitem">
                                    <i class="fas fa-file-excel"></i> Laporan Mingguan
                                </button>
                                <button type="button" class="sales-download-menu-item" data-sales-period="monthly" role="menuitem">
                                    <i class="fas fa-file-excel"></i> Laporan Bulanan
                                </button>
                                <button type="button" class="sales-download-menu-item" data-sales-period="filter" role="menuitem">
                                    <i class="fas fa-file-excel"></i> Sesuai Filter Tanggal
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle sales-table mb-0" id="sales-table">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Pelanggan</th>
                                    <th>Barang</th>
                                    <th>Total belanja</th>
                                    <th>Potongan harga</th>
                                    <th>Uang dibayar</th>
                                    <th>Kembalian</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
                <div class="sales-table-footer">
                    <div class="sales-table-meta" id="sales-table-meta"></div>
                    <div class="sales-pagination" id="sales-pagination"></div>
                </div>
            </div>

            <!-- Modal detail transaksi -->
            <div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="detailModalLabel">Detail Transaksi</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div id="detail-content">Memuat...</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal edit transaksi -->
            <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editModalLabel">Ubah Data Transaksi</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="editForm">
                                <input type="hidden" id="edit-sale-id">
                                <div class="mb-3">
                                    <label class="form-label">Nama pembeli</label>
                                    <input type="text" id="edit-customer-name" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Nomor telepon</label>
                                    <input type="text" id="edit-customer-phone" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Potongan harga (%)</label>
                                    <input type="number" id="edit-discount" class="form-control" step="0.01" min="0">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Uang yang dibayar (Rp)</label>
                                    <input type="number" id="edit-payment-amount" class="form-control" step="0.01" min="0">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Kembalian (Rp)</label>
                                    <input type="number" id="edit-change-amount" class="form-control" step="0.01" min="0">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Cara bayar</label>
                                    <select id="edit-payment-method" class="form-select">
                                        <option value="cash">Tunai</option>
                                        <option value="transfer">Transfer bank</option>
                                        <option value="card">Kartu</option>
                                        <option value="credit">Piutang (belum lunas)</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Keadaan transaksi</label>
                                    <select id="edit-status" class="form-select">
                                        <option value="completed">Selesai</option>
                                        <option value="pending">Belum selesai</option>
                                        <option value="cancelled">Dibatalkan</option>
                                        <option value="deleted">Dihapus dari laporan</option>
                                    </select>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="button" class="btn btn-primary" id="saveEditBtn">Simpan</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function getCsrfToken() {
            return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        }

        function getCsrfTokenFromCookie() {
            const match = document.cookie.split('; ').find(row => row.startsWith('XSRF-TOKEN='));
            if (!match) return null;
            return decodeURIComponent(match.split('=')[1]);
        }

        function monthToRange(ym) {
            const [y, m] = ym.split('-').map(x => parseInt(x, 10));
            const start = new Date(y, m - 1, 1);
            const end = new Date(y, m, 0);
            const pad = (n) => String(n).padStart(2, '0');
            const fmt = (d) => `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
            return { start: fmt(start), end: fmt(end) };
        }

        function formatRupiahFull(n) {
            return 'Rp ' + Math.round(Number(n) || 0).toLocaleString('id-ID');
        }

        function formatRupiahShort(v) {
            const x = Math.round(Number(v) || 0);
            if (x >= 1_000_000_000) {
                const j = x / 1_000_000_000;
                return 'Rp ' + (Number.isInteger(j) ? j : j.toFixed(1)).toString().replace(/\.0$/, '') + 'M';
            }
            if (x >= 1_000_000) {
                const j = x / 1_000_000;
                return 'Rp ' + (Number.isInteger(j) ? j : j.toFixed(1)).toString().replace(/\.0$/, '') + 'jt';
            }
            if (x >= 1_000) {
                const r = Math.round(x / 1_000);
                return 'Rp ' + r + 'rb';
            }
            return 'Rp ' + x.toLocaleString('id-ID');
        }

        function paymentLabel(method) {
            const m = String(method || '').toLowerCase();
            if (m === 'cash') return 'Tunai';
            if (m === 'transfer') return 'Transfer bank';
            if (m === 'card') return 'Kartu';
            if (m === 'credit') return 'Hutang (belum bayar)';
            return 'Cara lain';
        }

        function paymentColor(i) {
            const colors = ['#1a5c42', '#1d4ed8', '#ba7517', '#a32d2d', '#5c4a3a', '#3d2e22'];
            return colors[i % colors.length];
        }

        const SALES_CHART = {
            text: '#1e1710',
            muted: '#3d2e22',
            grid: 'rgba(30, 23, 16, 0.14)',
            bar: 'rgba(26, 92, 66, 0.82)',
            barBorder: '#144a35',
        };

        let salesPage = 1;
        const salesPerPage = 10;

        function escapeHtml(text) {
            if (!text) return '';
            const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
            return String(text).replace(/[&<>"']/g, (m) => map[m]);
        }

        function renderSalesPager(meta) {
            const pager = document.getElementById('sales-pagination');
            const info = document.getElementById('sales-table-meta');
            if (info && meta && meta.total != null) {
                const from = meta.from || 0;
                const to = meta.to || 0;
                const total = meta.total || 0;
                info.innerHTML = total
                    ? `Menampilkan <strong>${from}</strong>–<strong>${to}</strong> dari <strong>${total}</strong> transaksi`
                    : '';
            }
            if (!pager) return;
            if (!meta || !meta.last_page || meta.last_page <= 1) {
                pager.innerHTML = '';
                return;
            }

            const current = Number(meta.current_page || 1);
            const last = Number(meta.last_page || 1);
            const pages = [];
            const add = (p) => pages.push(p);
            add(1);
            if (current - 1 > 2) add('…');
            for (let p = Math.max(2, current - 1); p <= Math.min(last - 1, current + 1); p++) add(p);
            if (current + 1 < last - 1) add('…');
            if (last > 1) add(last);

            pager.innerHTML = `
                <ul class="pagination">
                    <li class="page-item ${current <= 1 ? 'disabled' : ''}">
                        <a class="page-link" href="#" data-page="${current - 1}" aria-label="Sebelumnya">‹</a>
                    </li>
                    ${pages
                        .map((p) => {
                            if (p === '…') return `<li class="page-item disabled"><span class="page-link">…</span></li>`;
                            const active = Number(p) === current ? 'active' : '';
                            return `<li class="page-item ${active}"><a class="page-link" href="#" data-page="${p}">${p}</a></li>`;
                        })
                        .join('')}
                    <li class="page-item ${current >= last ? 'disabled' : ''}">
                        <a class="page-link" href="#" data-page="${current + 1}" aria-label="Selanjutnya">›</a>
                    </li>
                </ul>
            `;

            pager.querySelectorAll('a.page-link[data-page]').forEach((a) => {
                a.addEventListener('click', (e) => {
                    e.preventDefault();
                    const p = Number(a.getAttribute('data-page') || 1);
                    if (!Number.isFinite(p) || p < 1 || p > last || p === current) return;
                    salesPage = p;
                    loadSales();
                });
            });
        }

        async function loadSales() {
            try {
                const csrfToken = getCsrfToken();
                const startDate = document.getElementById('start-date')?.value || '';
                const endDate = document.getElementById('end-date')?.value || '';
                const product = document.getElementById('search-product')?.value || '';
                const customer = document.getElementById('search-customer')?.value || '';
                const showCancelled = document.getElementById('show-cancelled')?.checked || false;

                const qs = new URLSearchParams();
                if (startDate && endDate) {
                    qs.set('start_date', startDate);
                    qs.set('end_date', endDate);
                }
                if (product.trim()) qs.set('product', product.trim());
                if (customer.trim()) qs.set('customer', customer.trim());
                if (showCancelled) qs.set('show_cancelled', 'true');
                qs.set('page', String(salesPage));
                qs.set('per_page', String(salesPerPage));

                const res = await fetch('/api/reports/sales' + (qs.toString() ? `?${qs.toString()}` : ''), {
                    credentials: 'include',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }
                });

                const raw = await res.text();
                let data = null;
                try { data = raw ? JSON.parse(raw) : null; } catch (e) { data = null; }

                const tbody = document.querySelector('#sales-table tbody');
                if (!res.ok) {
                    if (res.status === 401 || (data && data.message === 'Unauthenticated.')) {
                        tbody.innerHTML = '<tr><td colspan="8" class="text-center text-danger py-4">Silakan masuk terlebih dahulu untuk melihat laporan.</td></tr>';
                        return;
                    }
                    tbody.innerHTML = '<tr><td colspan="8" class="text-center text-danger py-4">Tidak bisa memuat data. Coba lagi nanti.</td></tr>';
                    return;
                }
                tbody.innerHTML = '';

                const filteredData = data.data || [];
                renderSalesPager(data.meta || null);
                if (filteredData.length > 0) {
                    filteredData.forEach(sale => {
                        const saleDate = new Date(sale.sale_date || sale.date);
                        const formattedDate = saleDate.toLocaleDateString('id-ID', {
                            year: 'numeric', month: '2-digit', day: '2-digit'
                        });
                        tbody.innerHTML += `
                            <tr>
                                <td>${formattedDate}</td>
                                <td>${escapeHtml(sale.customer_name || sale.customer || '-')}</td>
                                <td>${escapeHtml(sale.products || '-')}</td>
                                <td class="sales-td-money">Rp${parseFloat(sale.total_amount || sale.total || 0).toLocaleString('id-ID')}</td>
                                <td class="sales-td-money">Rp${parseFloat(sale.discount || 0).toLocaleString('id-ID')}</td>
                                <td class="sales-td-money">Rp${parseFloat(sale.payment_amount || sale.payment || 0).toLocaleString('id-ID')}</td>
                                <td class="sales-td-money">Rp${parseFloat(sale.change_amount || sale.change || 0).toLocaleString('id-ID')}</td>
                                <td>
                                    <div class="sales-act-group">
                                        <button type="button" class="btn sales-act-btn sales-act-btn--view detail-btn" data-sale-id="${sale.id}">
                                            <i class="fas fa-eye" aria-hidden="true"></i> Lihat
                                        </button>
                                        <button type="button" class="btn sales-act-btn sales-act-btn--edit edit-btn" data-sale-id="${sale.id}">
                                            <i class="fas fa-pen" aria-hidden="true"></i> Ubah
                                        </button>
                                        <button type="button" class="btn sales-act-btn sales-act-btn--del delete-btn" data-sale-id="${sale.id}">
                                            <i class="fas fa-trash" aria-hidden="true"></i> Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>`;
                    });

                    document.querySelectorAll('.detail-btn').forEach(btn => {
                        btn.addEventListener('click', function() { showDetail(this.getAttribute('data-sale-id')); });
                    });
                    document.querySelectorAll('.edit-btn').forEach(btn => {
                        btn.addEventListener('click', function() { openEditModal(this.getAttribute('data-sale-id')); });
                    });
                    document.querySelectorAll('.delete-btn').forEach(btn => {
                        btn.addEventListener('click', function() {
                            const saleId = this.getAttribute('data-sale-id');
                            if (confirm('Yakin ingin menghapus transaksi ini dari laporan?')) {
                                const token = getCsrfToken();
                                fetch(`/api/reports/sales/${saleId}`, {
                                    method: 'DELETE',
                                    credentials: 'same-origin',
                                    headers: {
                                        'X-CSRF-TOKEN': token,
                                        'Content-Type': 'application/json',
                                        'Accept': 'application/json'
                                    }
                                })
                                .then(response => { if (!response.ok) throw new Error('Gagal menghapus'); return response.json(); })
                                .then(() => { alert('Transaksi berhasil dihapus'); loadSales(); loadAnalytics(); })
                                .catch(err => { console.error(err); alert(err.message); });
                            }
                        });
                    });
                } else {
                    tbody.innerHTML = '<tr><td colspan="8" class="text-center py-4 sales-muted-msg">Belum ada transaksi di rentang ini.</td></tr>';
                    renderSalesPager(data.meta || null);
                }
            } catch (error) {
                console.error('Error loading sales data:', error);
                document.querySelector('#sales-table tbody').innerHTML =
                    '<tr><td colspan="8" class="text-center text-danger py-4">Terjadi gangguan saat memuat data.</td></tr>';
                renderSalesPager(null);
            }
        }

        async function showDetail(id) {
            try {
                const res = await fetch(`/api/reports/sales/${id}`, {
                    credentials: 'same-origin',
                    headers: { 'Accept': 'application/json' }
                });
                if (!res.ok) throw new Error(`HTTP ${res.status}`);
                const data = await res.json();
                const sale = data.data || data;
                const saleDate = new Date(sale.sale_date || sale.date);
                const formattedDate = saleDate.toLocaleDateString('id-ID', {
                    year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit'
                });

                let html = `
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-primary">Ringkasan</h6>
                            <table class="table table-borderless table-sm">
                                <tr><td><strong>Tanggal</strong></td><td>${formattedDate}</td></tr>
                                <tr><td><strong>Pelanggan</strong></td><td>${sale.customer_name || sale.customer || '-'}</td></tr>
                                <tr><td><strong>Total belanja</strong></td><td>Rp${parseFloat(sale.total_amount || sale.total || 0).toLocaleString('id-ID')}</td></tr>
                                <tr><td><strong>Potongan</strong></td><td>${sale.discount || 0}%</td></tr>
                                <tr><td><strong>Uang dibayar</strong></td><td>Rp${parseFloat(sale.payment_amount || sale.payment || 0).toLocaleString('id-ID')}</td></tr>
                                <tr><td><strong>Kembalian</strong></td><td>Rp${parseFloat(sale.change_amount || sale.change || 0).toLocaleString('id-ID')}</td></tr>
                            </table>
                        </div>
                    </div>
                    <hr>
                    <h6 class="text-primary">Barang yang dibeli</h6>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead><tr><th>No</th><th>Nama barang</th><th>Harga satuan</th><th>Jumlah</th><th>Subtotal</th></tr></thead>
                            <tbody>`;

                if (sale.items && sale.items.length > 0) {
                    sale.items.forEach((item, index) => {
                        html += `
                            <tr>
                                <td>${index + 1}</td>
                                <td>${item.product_name || item.product?.name || '-'}</td>
                                <td>Rp${parseFloat(item.unit_price || item.price || 0).toLocaleString('id-ID')}</td>
                                <td>${item.quantity || item.qty || 0}</td>
                                <td>Rp${parseFloat(item.subtotal || 0).toLocaleString('id-ID')}</td>
                            </tr>`;
                    });
                } else {
                    html += '<tr><td colspan="5" class="text-center">Tidak ada rincian barang</td></tr>';
                }
                html += `</tbody></table></div>`;
                document.getElementById('detail-content').innerHTML = html;
                new bootstrap.Modal(document.getElementById('detailModal')).show();
            } catch (error) {
                document.getElementById('detail-content').innerHTML =
                    `<div class="alert alert-danger">Gagal memuat detail: ${error.message}</div>`;
                new bootstrap.Modal(document.getElementById('detailModal')).show();
            }
        }

        let salesDownloadBusy = false;

        function parseSalesFilenameFromDisposition(header) {
            if (!header) return null;
            const utf8 = header.match(/filename\*=UTF-8''([^;]+)/i);
            if (utf8) return decodeURIComponent(utf8[1].trim());
            const plain = header.match(/filename="?([^";]+)"?/i);
            return plain ? plain[1].trim() : null;
        }

        function closeSalesDownloadMenu() {
            document.querySelectorAll('.sales-download-wrap.is-open').forEach((wrap) => {
                wrap.classList.remove('is-open');
                const toggle = wrap.querySelector('.sales-download-toggle');
                if (toggle) toggle.setAttribute('aria-expanded', 'false');
            });
        }

        function openSalesDownloadMenu(wrap) {
            if (!wrap) return;
            closeSalesDownloadMenu();
            wrap.classList.add('is-open');
            const toggle = wrap.querySelector('.sales-download-toggle');
            if (toggle) toggle.setAttribute('aria-expanded', 'true');
        }

        function setSalesDownloadStatus(type, message) {
            const status = document.getElementById('sales-download-status');
            if (!status) return;
            status.classList.remove('is-loading', 'is-success', 'is-error');
            if (type) status.classList.add(type);
            status.textContent = message;
        }

        function setSalesDownloadLoading(active, message) {
            const toggles = document.querySelectorAll('.sales-download-toggle, #btn-sales-download-quick');
            const menuItems = document.querySelectorAll('.sales-download-menu-item[data-sales-period]');
            toggles.forEach((toggle) => {
                toggle.disabled = active;
                if (active && !toggle.dataset.originalHtml) {
                    toggle.dataset.originalHtml = toggle.innerHTML;
                    toggle.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span>Mengunduh...</span>';
                } else if (!active && toggle.dataset.originalHtml) {
                    toggle.innerHTML = toggle.dataset.originalHtml;
                    delete toggle.dataset.originalHtml;
                }
            });
            menuItems.forEach((btn) => { btn.disabled = active; });
            if (active) {
                closeSalesDownloadMenu();
                setSalesDownloadStatus('is-loading', message || 'Menyiapkan file Excel, mohon tunggu...');
            }
        }

        function initSalesDownloadMenu() {
            document.querySelectorAll('.sales-download-wrap').forEach((wrap) => {
                const toggle = wrap.querySelector('.sales-download-toggle');
                if (!toggle) return;

                toggle.addEventListener('click', function(e) {
                    e.stopPropagation();
                    if (salesDownloadBusy) return;
                    if (wrap.classList.contains('is-open')) closeSalesDownloadMenu();
                    else openSalesDownloadMenu(wrap);
                });
            });

            document.addEventListener('click', function(e) {
                if (!e.target.closest('.sales-download-wrap')) closeSalesDownloadMenu();
            });

            document.querySelectorAll('.sales-download-menu-item[data-sales-period]').forEach((btn) => {
                if (btn.dataset.boundSalesDownload) return;
                btn.dataset.boundSalesDownload = '1';
                btn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const period = btn.getAttribute('data-sales-period');
                    if (period && !salesDownloadBusy) {
                        closeSalesDownloadMenu();
                        downloadSalesReport(period);
                    }
                });
            });

            const quickBtn = document.getElementById('btn-sales-download-quick');
            if (quickBtn && !quickBtn.dataset.boundSalesDownload) {
                quickBtn.dataset.boundSalesDownload = '1';
                quickBtn.addEventListener('click', function() {
                    if (!salesDownloadBusy) downloadSalesReport('filter');
                });
            }
        }

        async function downloadSalesReport(period) {
            if (salesDownloadBusy) return;
            salesDownloadBusy = true;

            const periodLabels = {
                daily: 'harian',
                weekly: 'mingguan',
                monthly: 'bulanan',
                filter: 'sesuai filter tanggal',
            };
            const periodLabel = periodLabels[period] || period;
            setSalesDownloadLoading(true, `Menyiapkan laporan ${periodLabel}...`);

            const startDate = document.getElementById('start-date')?.value || '';
            const endDate = document.getElementById('end-date')?.value || '';
            const product = document.getElementById('search-product')?.value?.trim() || '';
            const customer = document.getElementById('search-customer')?.value?.trim() || '';
            const showCancelled = document.getElementById('show-cancelled')?.checked || false;

            const params = new URLSearchParams();
            if (period && period !== 'filter') {
                params.set('period', period);
                if (startDate) params.set('date', startDate);
            }
            if (startDate && endDate) {
                params.set('start_date', startDate);
                params.set('end_date', endDate);
            }
            if (product) params.set('product', product);
            if (customer) params.set('customer', customer);
            if (showCancelled) params.set('show_cancelled', 'true');

            const url = `/api/reports/download/sales/excel?${params.toString()}`;
            const defaultName = period === 'filter' && startDate && endDate
                ? `laporan_penjualan_${startDate}_sd_${endDate}.xlsx`
                : `laporan_penjualan_${period || 'filter'}.xlsx`;
            let downloadedName = null;

            try {
                const res = await fetch(url, { credentials: 'same-origin' });
                if (!res.ok) {
                    let msg = 'Gagal mengunduh laporan.';
                    try {
                        const err = await res.json();
                        if (err?.error) msg = err.error;
                    } catch (_) {}
                    throw new Error(msg);
                }
                const blob = await res.blob();
                downloadedName = parseSalesFilenameFromDisposition(res.headers.get('Content-Disposition')) || defaultName;
                const blobUrl = URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.href = blobUrl;
                link.download = downloadedName;
                document.body.appendChild(link);
                link.click();
                link.remove();
                setTimeout(() => URL.revokeObjectURL(blobUrl), 1000);
            } catch (error) {
                console.error('Download sales report failed:', error);
                setSalesDownloadStatus('is-error', error?.message || 'Gagal mengunduh laporan. Coba lagi.');
            } finally {
                salesDownloadBusy = false;
                setSalesDownloadLoading(false);
                if (downloadedName) {
                    setSalesDownloadStatus('is-success', `Berhasil mengunduh: ${downloadedName}`);
                }
            }
        }

        let dailyChart = null;

        function ensureChartJs(cb) {
            if (window.Chart) { cb(); return; }
            const s = document.createElement('script');
            s.src = 'https://cdn.jsdelivr.net/npm/chart.js';
            s.onload = () => cb();
            document.head.appendChild(s);
        }

        async function loadAnalytics() {
            const startDate = document.getElementById('start-date')?.value || '';
            const endDate = document.getElementById('end-date')?.value || '';
            const product = document.getElementById('search-product')?.value || '';
            const customer = document.getElementById('search-customer')?.value || '';
            const showCancelled = document.getElementById('show-cancelled')?.checked || false;

            const qs = new URLSearchParams();
            if (startDate && endDate) {
                qs.set('start_date', startDate);
                qs.set('end_date', endDate);
            }
            if (product.trim()) qs.set('product', product.trim());
            if (customer.trim()) qs.set('customer', customer.trim());
            if (showCancelled) qs.set('show_cancelled', 'true');

            try {
                const res = await fetch('/api/reports/sales-analytics' + (qs.toString() ? `?${qs.toString()}` : ''));
                const json = await res.json();
                if (!res.ok) throw new Error(json.error || 'Gagal memuat ringkasan');

                const sum = json.summary || {};
                const totalTx = sum.total_transactions || 0;
                const cashDirect = sum.cash_direct ?? 0;
                const credit = sum.total_credit || 0;
                const cancelled = sum.cancelled_count ?? 0;

                document.getElementById('kpi-total-tx').textContent = totalTx.toLocaleString('id-ID');
                document.getElementById('kpi-total-tx-tag').textContent = totalTx === 1 ? '1 kali transaksi' : `${totalTx.toLocaleString('id-ID')} kali transaksi`;
                document.getElementById('kpi-cash').textContent = formatRupiahFull(cashDirect);
                document.getElementById('kpi-credit').textContent = formatRupiahFull(credit);
                document.getElementById('kpi-cancelled').textContent = cancelled.toLocaleString('id-ID');

                const emptyEl = document.getElementById('report-empty-state');
                if (totalTx === 0) emptyEl.classList.add('is-visible');
                else emptyEl.classList.remove('is-visible');

                const daily = json.charts?.daily || [];
                const labels = daily.map(x => {
                    const d = new Date(x.date + 'T12:00:00');
                    return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short' });
                });
                const values = daily.map(x => x.total_sales);

                ensureChartJs(() => {
                    const ctx = document.getElementById('dailySalesChart').getContext('2d');
                    if (dailyChart) dailyChart.destroy();
                    dailyChart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels,
                            datasets: [{
                                label: 'Uang masuk',
                                data: values,
                                backgroundColor: SALES_CHART.bar,
                                borderColor: SALES_CHART.barBorder,
                                borderWidth: 2,
                                borderRadius: 6,
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { legend: { display: false } },
                            scales: {
                                x: {
                                    ticks: {
                                        color: SALES_CHART.muted,
                                        font: { size: 13, weight: '600' },
                                        maxRotation: 45,
                                        minRotation: 0,
                                    },
                                    grid: { color: SALES_CHART.grid, lineWidth: 1 },
                                    border: { color: SALES_CHART.grid },
                                },
                                y: {
                                    ticks: {
                                        color: SALES_CHART.muted,
                                        font: { size: 13, weight: '600' },
                                        callback: (v) => formatRupiahShort(v),
                                    },
                                    grid: { color: SALES_CHART.grid, lineWidth: 1 },
                                    border: { color: SALES_CHART.grid },
                                },
                            },
                        },
                    });
                });

                const topProducts = json.charts?.top_products || [];
                const topEl = document.getElementById('top-products-list');
                if (!topProducts.length) {
                    topEl.innerHTML = '<p class="sales-muted-msg">Belum ada data barang terjual di rentang ini.</p>';
                } else {
                    const maxQty = Math.max(...topProducts.map(p => p.qty), 1);
                    topEl.innerHTML = topProducts.map(p => {
                        const w = Math.round((p.qty / maxQty) * 100);
                        return `
                            <div class="sales-top-item">
                                <div class="sales-top-head">
                                    <span class="sales-top-name">${escapeHtml(p.name)}</span>
                                    <span class="sales-top-qty">${p.qty.toLocaleString('id-ID')} unit</span>
                                </div>
                                <div class="sales-top-bar"><span class="sales-top-fill" style="width:${w}%"></span></div>
                            </div>`;
                    }).join('');
                }

                const pm = json.charts?.payment_methods || [];
                const payEl = document.getElementById('payment-methods-list');
                const totalAmt = pm.reduce((a, x) => a + (Number(x.amount) || 0), 0);
                if (!pm.length) {
                    payEl.innerHTML = '<p class="sales-muted-msg">Belum ada pembagian cara bayar.</p>';
                } else {
                    payEl.innerHTML = pm.map((x, i) => {
                        const amt = Number(x.amount) || 0;
                        const pct = totalAmt > 0 ? Math.round((amt / totalAmt) * 100) : 0;
                        return `
                            <div class="sales-pay-row">
                                <span class="sales-pay-dot" style="background:${paymentColor(i)}"></span>
                                <div>
                                    <div class="sales-pay-name">${paymentLabel(x.method)}</div>
                                    <div class="sales-pay-meta">${formatRupiahFull(amt)} · ${x.count || 0} kali · ${pct}% dari total nilai</div>
                                </div>
                            </div>`;
                    }).join('');
                }
            } catch (e) {
                console.error('Analytics error:', e);
            }
        }

        async function openEditModal(id) {
            try {
                const res = await fetch(`/api/reports/sales/${id}`, { credentials: 'same-origin', headers: { 'Accept': 'application/json' } });
                if (!res.ok) throw new Error('Gagal memuat data');
                const json = await res.json();
                const sale = json.data || json;
                document.getElementById('edit-sale-id').value = sale.id || id;
                document.getElementById('edit-customer-name').value = sale.customer_name || '';
                document.getElementById('edit-customer-phone').value = sale.customer_phone || '';
                document.getElementById('edit-discount').value = sale.discount ?? 0;
                document.getElementById('edit-payment-amount').value = sale.payment_amount ?? 0;
                document.getElementById('edit-change-amount').value = sale.change_amount ?? 0;
                document.getElementById('edit-payment-method').value = sale.payment_method || 'cash';
                document.getElementById('edit-status').value = sale.status || 'completed';
                new bootstrap.Modal(document.getElementById('editModal')).show();
            } catch (err) {
                alert('Gagal membuka ubah data: ' + (err.message || err));
            }
        }

        document.getElementById('saveEditBtn').addEventListener('click', async function() {
            const id = document.getElementById('edit-sale-id').value;
            const payload = {
                customer_name: document.getElementById('edit-customer-name').value,
                customer_phone: document.getElementById('edit-customer-phone').value,
                discount: parseFloat(document.getElementById('edit-discount').value) || 0,
                payment_amount: parseFloat(document.getElementById('edit-payment-amount').value) || 0,
                change_amount: parseFloat(document.getElementById('edit-change-amount').value) || 0,
                payment_method: document.getElementById('edit-payment-method').value,
                status: document.getElementById('edit-status').value,
            };
            try {
                const csrf = getCsrfTokenFromCookie();
                const res = await fetch(`/api/reports/sales/${id}`, {
                    method: 'PUT',
                    credentials: 'same-origin',
                    headers: Object.assign({
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }, csrf ? { 'X-XSRF-TOKEN': csrf } : {}),
                    body: JSON.stringify(payload)
                });
                if (!res.ok) {
                    const errJson = await res.json().catch(() => ({}));
                    throw new Error(errJson.error || 'Gagal menyimpan');
                }
                bootstrap.Modal.getInstance(document.getElementById('editModal')).hide();
                alert('Perubahan tersimpan');
                loadSales();
                loadAnalytics();
            } catch (err) {
                alert('Gagal menyimpan: ' + (err.message || err));
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            initSalesDownloadMenu();

            const monthInput = document.getElementById('filter-month');
            if (monthInput) {
                monthInput.addEventListener('change', () => {
                    const r = monthToRange(monthInput.value);
                    document.getElementById('start-date').value = r.start;
                    document.getElementById('end-date').value = r.end;
                });
            }

            document.getElementById('btn-apply-filters').addEventListener('click', () => {
                salesPage = 1;
                loadSales();
                loadAnalytics();
            });

            // HCD: cari langsung saat mengetik (tanpa harus scroll / klik)
            const debounce = (fn, wait = 350) => {
                let t = null;
                return (...args) => {
                    if (t) clearTimeout(t);
                    t = setTimeout(() => fn(...args), wait);
                };
            };
            const applyLive = debounce(() => {
                salesPage = 1;
                loadSales();
                loadAnalytics();
            }, 350);

            const productInput = document.getElementById('search-product');
            const customerInput = document.getElementById('search-customer');
            [productInput, customerInput].forEach((el) => {
                if (!el) return;
                el.addEventListener('input', applyLive);
                el.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        loadSales();
                        loadAnalytics();
                    }
                });
            });
            document.getElementById('btn-reset-filters').addEventListener('click', () => {
                document.getElementById('filter-month').selectedIndex = 0;
                const r = monthToRange(document.getElementById('filter-month').value);
                document.getElementById('start-date').value = r.start;
                document.getElementById('end-date').value = r.end;
                document.getElementById('search-product').value = '';
                document.getElementById('search-customer').value = '';
                document.getElementById('show-cancelled').checked = false;
                salesPage = 1;
                loadSales();
                loadAnalytics();
            });

            loadSales();
            loadAnalytics();
        });
    </script>
@endpush
