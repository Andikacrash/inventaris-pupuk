@extends('layouts.app')

@push('styles')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .stock-report-page { font-family: var(--wn-font, 'Segoe UI', system-ui, sans-serif); color: #1e1710; font-size: 16px; line-height: 1.65; }
        .stock-shell { background: #ede8e1; border: 0.5px solid #c8bfb4; border-radius: 12px; padding: 20px 24px; }
        .stock-header { display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; margin-bottom: 16px; }
        .stock-title { margin: 0; font-size: 1.5rem; font-weight: 500; color: #1e1710; }
        .stock-subtitle { margin: 8px 0 0; color: #4a3728; font-size: 16px; }
        .stock-download-wrap {
            position: relative;
            min-width: 220px;
        }
        .stock-download-toggle {
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
        .stock-download-toggle:hover, .stock-download-toggle:focus {
            background: #256b4f;
            border-color: #256b4f;
            color: #f8faf8;
        }
        .stock-download-toggle:disabled { opacity: 0.75; cursor: wait; }
        .stock-download-chevron {
            font-size: 12px;
            transition: transform 0.2s ease;
        }
        .stock-download-wrap.is-open .stock-download-chevron {
            transform: rotate(180deg);
        }
        .stock-download-menu {
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
        .stock-download-wrap.is-open .stock-download-menu {
            display: flex;
        }
        .stock-download-menu-item {
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
        .stock-download-menu-item:hover, .stock-download-menu-item:focus {
            background: rgba(26, 92, 66, 0.12);
            border-color: #1a5c42;
            color: #1a5c42;
        }
        .stock-download-menu-item:disabled {
            opacity: 0.6;
            cursor: wait;
        }
        .stock-download-menu-item i { color: #1a5c42; width: 18px; }
        .stock-download-status {
            font-size: 13px;
            color: #4a3728;
            margin-top: 8px;
            min-height: 20px;
            line-height: 1.4;
        }
        .stock-download-status.is-loading { color: #1a5c42; font-weight: 600; }
        .stock-download-status.is-success { color: #1a5c42; font-weight: 600; }
        .stock-download-status.is-error { color: #a32d2d; font-weight: 600; }
        .stock-kpi-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); margin-bottom: 16px; border-radius: 12px; background: #ede8e1; border: 0.5px solid #c8bfb4; }
        .stock-kpi { padding: 16px; }
        .stock-kpi:not(:last-child) { border-right: 0.5px solid #c8bfb4; }
        .stock-kpi-label { font-size: 14px; color: #4a3728; margin-bottom: 8px; font-weight: 500; }
        .stock-kpi-value { font-size: 1.2rem; color: #1e1710; line-height: 1.25; font-weight: 600; }
        .stock-kpi-value.is-in { color: #1a5c42; }
        .stock-kpi-value.is-out { color: #a32d2d; }
        .stock-kpi-value.is-balance { color: #ba7517; }
        .stock-kpi-value.is-negative { color: #a32d2d; }
        .stock-kpi-badge { margin-top: 8px; display: inline-flex; align-items: center; border-radius: 999px; border: 0.5px solid #c8bfb4; color: #4a3728; font-size: 13px; padding: 3px 10px; }
        .stock-kpi-badge.is-good { border-color: rgba(26, 92, 66, 0.4); color: #1a5c42; background: rgba(26, 92, 66, 0.1); }
        .stock-kpi-badge.is-bad { border-color: rgba(163, 45, 45, 0.4); color: #a32d2d; background: rgba(163, 45, 45, 0.08); }
        .stock-kpi-badge.is-amber { border-color: rgba(186, 117, 23, 0.45); color: #ba7517; background: rgba(186, 117, 23, 0.1); }
        .stock-warning-box { border: 0.5px solid rgba(186, 117, 23, 0.5); border-radius: 12px; background: rgba(186, 117, 23, 0.12); color: #4a3728; font-size: 16px; padding: 12px 14px; margin-bottom: 16px; display: none; }
        .stock-insights { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; margin-bottom: 16px; }
        .stock-insight-card { border: 0.5px solid #c8bfb4; border-radius: 12px; background: #ede8e1; padding: 16px; }
        .stock-insight-title { margin: 0 0 8px; font-size: 14px; color: #4a3728; font-weight: 500; }
        .stock-insight-text { margin: 0; color: #1e1710; font-size: 16px; }
        .stock-insight-card.warn { border-color: rgba(186, 117, 23, 0.5); background: rgba(186, 117, 23, 0.1); }
        .stock-insight-card.warn .stock-insight-title, .stock-insight-card.warn .stock-insight-text { color: #ba7517; }
        .stock-filters { border: 0.5px solid #c8bfb4; border-radius: 12px; background: #ede8e1; padding: 16px; margin-bottom: 16px; }
        .stock-filter-head { font-size: 1rem; font-weight: 500; color: #1e1710; margin-bottom: 12px; }
        .stock-filter-label { display: block; margin-bottom: 8px; font-size: 14px; font-weight: 500; color: #4a3728; }
        .stock-filter-actions { margin-top: 12px; display: flex; gap: 12px; justify-content: flex-end; }
        .stock-btn-primary { border: 0.5px solid #1a5c42; background: #1a5c42; color: #f8faf8; border-radius: 12px; font-size: 16px; padding: 10px 16px; min-height: 48px; font-weight: 500; }
        .stock-btn-outline { border: 0.5px solid #c8bfb4; background: #f4f1ec; color: #1e1710; border-radius: 12px; font-size: 16px; padding: 10px 16px; min-height: 48px; font-weight: 500; }
        .stock-btn-primary:hover, .stock-btn-primary:focus { background: #256b4f; border-color: #256b4f; color: #f8faf8; }
        .stock-table-card { border: 0.5px solid #c8bfb4; border-radius: 12px; background: #ede8e1; overflow: hidden; }
        .stock-legend { margin: 12px 14px 0; display: flex; align-items: center; gap: 16px; font-size: 14px; color: #4a3728; }
        .stock-legend-item { display: inline-flex; align-items: center; gap: 6px; }
        .stock-legend-dot { width: 10px; height: 10px; border-radius: 999px; display: inline-block; }
        .stock-legend-dot.in { background: #1a5c42; }
        .stock-legend-dot.out { background: #a32d2d; }
        .stock-table thead th { background: #f4f1ec; color: #4a3728; font-size: 14px; font-weight: 500; border-bottom: 0.5px solid #c8bfb4; padding: 12px; }
        .stock-table tbody td { font-size: 16px; color: #1e1710; border-color: #c8bfb4; background: #ede8e1; }
        .stock-table tbody tr:hover td { background: rgba(244, 241, 236, 0.85); }
        .stock-delta { font-weight: 600; }
        .stock-delta.in { color: #1a5c42; }
        .stock-delta.out { color: #a32d2d; }
        .stock-change-cell { min-width: 150px; }
        .stock-change-main { font-weight: 600; color: #1e1710; font-size: 15px; }
        .stock-change-sub { margin-top: 4px; font-size: 14px; display: inline-flex; align-items: center; gap: 4px; }
        .stock-change-sub.in { color: #1a5c42; }
        .stock-change-sub.out { color: #a32d2d; }
        .stock-table-footer { border-top: 0.5px solid #c8bfb4; padding: 12px 14px; display: flex; justify-content: space-between; align-items: center; gap: 10px; flex-wrap: wrap; font-size: 15px; color: #4a3728; }
        .stock-pagination { display: inline-flex; align-items: center; gap: 8px; }
        .stock-page-btn { border: 0.5px solid #c8bfb4; border-radius: 10px; background: #f4f1ec; color: #1e1710; font-size: 14px; padding: 8px 12px; min-height: 44px; }
        .stock-page-btn:disabled { opacity: 0.5; cursor: not-allowed; }
        @media (max-width: 991.98px) {
            .stock-kpi-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .stock-kpi:nth-child(2n) { border-right: 0; }
            .stock-kpi:nth-child(1), .stock-kpi:nth-child(2) { border-bottom: 1px solid #e2e8f0; }
            .stock-insights { grid-template-columns: 1fr; }
        }
        @media (max-width: 767.98px) { .stock-header { flex-direction: column; align-items: stretch; } }
    </style>
@endpush

@section('content')
    <div class="stock-report-page">
        <div class="stock-shell">
            <div class="stock-header">
                <div>
                    <h2 class="stock-title">Riwayat Keluar Masuk Barang</h2>
                    <p class="stock-subtitle">Halaman sederhana untuk melihat barang masuk dan keluar setiap hari.</p>
                </div>
                <div class="stock-download-wrap" id="stock-download-wrap">
                    <button type="button" class="stock-download-toggle" id="btn-stock-download-toggle" aria-expanded="false" aria-haspopup="true">
                        <i class="fas fa-download"></i>
                        <span>Unduh Laporan</span>
                        <i class="fas fa-chevron-down stock-download-chevron" aria-hidden="true"></i>
                    </button>
                    <div class="stock-download-menu" id="stock-download-menu" role="menu">
                        <button type="button" class="stock-download-menu-item" data-stock-period="daily" role="menuitem">
                            <i class="fas fa-file-excel"></i> Laporan Harian
                        </button>
                        <button type="button" class="stock-download-menu-item" data-stock-period="weekly" role="menuitem">
                            <i class="fas fa-file-excel"></i> Laporan Mingguan
                        </button>
                        <button type="button" class="stock-download-menu-item" data-stock-period="monthly" role="menuitem">
                            <i class="fas fa-file-excel"></i> Laporan Bulanan
                        </button>
                    </div>
                    <p class="stock-download-status mb-0" id="stock-download-status" aria-live="polite">
                        Klik tombol di atas untuk pilih periode Excel.
                    </p>
                </div>
            </div>

            <div class="stock-kpi-grid">
                <div class="stock-kpi">
                    <div class="stock-kpi-label">Total Transaksi</div>
                    <div class="stock-kpi-value" id="sum-movements">0</div>
                    <span class="stock-kpi-badge" id="badge-movements">semua catatan keluar masuk</span>
                </div>
                <div class="stock-kpi">
                    <div class="stock-kpi-label">Barang Masuk</div>
                    <div class="stock-kpi-value is-in" id="sum-in">0</div>
                    <span class="stock-kpi-badge is-good" id="badge-in">unit diterima</span>
                </div>
                <div class="stock-kpi">
                    <div class="stock-kpi-label">Barang Keluar</div>
                    <div class="stock-kpi-value is-out" id="sum-out">0</div>
                    <span class="stock-kpi-badge is-bad" id="badge-out">unit terjual/dipakai</span>
                </div>
                <div class="stock-kpi">
                    <div class="stock-kpi-label">Selisih Stok</div>
                    <div class="stock-kpi-value is-balance" id="sum-net">0</div>
                    <span class="stock-kpi-badge is-amber" id="badge-net">lebih banyak keluar</span>
                </div>
            </div>

            <div class="stock-warning-box" id="stock-warning-box">
                Barang lebih banyak keluar daripada masuk. Pertimbangkan segera restock.
            </div>

            <div class="stock-insights">
                <div class="stock-insight-card" id="insight-ratio-card">
                    <p class="stock-insight-title">Peringatan Rasio Keluar/Masuk</p>
                    <p class="stock-insight-text" id="insight-ratio-text">Menyiapkan data rasio pergerakan...</p>
                </div>
                <div class="stock-insight-card" id="insight-product-card">
                    <p class="stock-insight-title">Barang Paling Aktif</p>
                    <p class="stock-insight-text" id="insight-product-text">Mengidentifikasi barang paling aktif...</p>
                </div>
            </div>

            <div class="stock-filters">
                <div class="stock-filter-head">Cari &amp; Saring Data</div>
                <div class="row g-2">
                    <div class="col-6 col-md-3">
                        <label for="period" class="stock-filter-label">Rentang Waktu</label>
                        <select class="form-select" id="period">
                            <option value="" selected>Semua periode</option>
                            <option value="daily">Harian</option>
                            <option value="weekly">Mingguan</option>
                            <option value="monthly">Bulanan</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-3">
                        <label for="date_start" class="stock-filter-label">Mulai Tanggal</label>
                        <input type="date" class="form-control" id="date_start" value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="col-md-3">
                        <label for="product" class="stock-filter-label">Nama Barang</label>
                        <select class="form-select" id="product">
                            <option value="">Semua produk</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="type" class="stock-filter-label">Jenis Pergerakan</label>
                        <select class="form-select" id="type">
                            <option value="">Semua tipe</option>
                            <option value="in">Masuk</option>
                            <option value="out">Keluar</option>
                        </select>
                    </div>
                </div>
                <div class="stock-filter-actions">
                    <button type="button" class="stock-btn-primary" id="btn-apply">Tampilkan Data</button>
                    <button type="button" class="stock-btn-outline" id="btn-reset">Reset</button>
                </div>
            </div>

            <div class="stock-table-card">
                <div class="stock-legend">
                    <span class="stock-legend-item"><span class="stock-legend-dot in"></span>hijau = stok bertambah</span>
                    <span class="stock-legend-item"><span class="stock-legend-dot out"></span>merah = stok berkurang</span>
                </div>
                <div class="table-responsive">
                    <table class="table stock-table align-middle mb-0" id="stock-table">
                        <thead>
                            <tr>
                                <th>Tanggal &amp; Jam</th>
                                <th>Nama Barang</th>
                                <th>Jenis</th>
                                <th class="text-end">Jumlah</th>
                                <th>Perubahan Stok</th>
                                <th class="text-end">Stok Sekarang</th>
                                <th id="notes-col-head">Keterangan</th>
                                <th>Petugas</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
                <div class="stock-table-footer">
                    <span id="stock-result-count">0 hasil</span>
                    <div class="stock-pagination">
                        <button class="stock-page-btn" id="page-prev" type="button">Sebelumnya</button>
                        <span id="page-indicator">Hal. 1/1</span>
                        <button class="stock-page-btn" id="page-next" type="button">Berikutnya</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const stockState = { rows: [], page: 1, perPage: 12 };

        function getReferenceDateValue() { return document.getElementById('date_start')?.value || ''; }

        function buildStockQueryUrl() {
            const params = new URLSearchParams();
            const period = document.getElementById('period')?.value;
            const date = getReferenceDateValue();
            const product = document.getElementById('product')?.value;
            const type = document.getElementById('type')?.value;
            if (period) params.set('period', period);
            if (date) params.set('date', date);
            if (product) params.set('product_id', product);
            if (type) params.set('type', type);
            const q = params.toString();
            return '/api/reports/stock' + (q ? '?' + q : '');
        }

        function updateKpi(summary) {
            const movements = Number(summary?.total_movements || 0);
            const totalIn = Number(summary?.total_in || 0);
            const totalOut = Number(summary?.total_out || 0);
            const net = Number(summary?.net_change || 0);
            document.getElementById('sum-movements').textContent = movements.toLocaleString('id-ID');
            document.getElementById('sum-in').textContent = totalIn.toLocaleString('id-ID');
            document.getElementById('sum-out').textContent = totalOut.toLocaleString('id-ID');
            document.getElementById('sum-net').textContent = `${net > 0 ? '+' : ''}${net.toLocaleString('id-ID')}`;
            const netEl = document.getElementById('sum-net');
            netEl.classList.toggle('is-negative', net < 0);
            netEl.classList.toggle('is-in', net > 0);
            netEl.classList.toggle('is-balance', net === 0);
            document.getElementById('badge-movements').textContent = `${movements.toLocaleString('id-ID')} transaksi tercatat`;
            document.getElementById('badge-in').textContent = `${totalIn.toLocaleString('id-ID')} unit diterima`;
            document.getElementById('badge-out').textContent = `${totalOut.toLocaleString('id-ID')} unit terjual/dipakai`;
            document.getElementById('badge-net').textContent = net < 0 ? 'lebih banyak keluar' : (net > 0 ? 'lebih banyak masuk' : 'jumlah seimbang');
            document.getElementById('stock-warning-box').style.display = totalOut > totalIn ? 'block' : 'none';
        }

        function updateInsights(rows, summary) {
            const inVal = Number(summary?.total_in || 0);
            const outVal = Number(summary?.total_out || 0);
            const ratio = inVal > 0 ? outVal / inVal : (outVal > 0 ? Infinity : 0);
            const ratioCard = document.getElementById('insight-ratio-card');
            const ratioText = document.getElementById('insight-ratio-text');
            ratioCard.classList.toggle('warn', ratio > 5);
            ratioText.textContent = ratio > 5
                ? `Peringatan: barang keluar ${ratio.toFixed(1)}x lebih tinggi dibanding barang masuk.`
                : `Rasio keluar/masuk ${Number.isFinite(ratio) ? ratio.toFixed(1) : '0.0'}x, masih aman.`;
            const productCount = {};
            rows.forEach((row) => { const name = row.product?.name || 'Tanpa nama'; productCount[name] = (productCount[name] || 0) + 1; });
            const topEntry = Object.entries(productCount).sort((a, b) => b[1] - a[1])[0];
            document.getElementById('insight-product-text').textContent = topEntry
                ? `${topEntry[0]} paling sering bergerak (${topEntry[1]} transaksi).`
                : 'Belum ada barang aktif pada filter saat ini.';
        }

        function renderTable() {
            const tbody = document.querySelector('#stock-table tbody');
            const rows = stockState.rows || [];
            const total = rows.length;
            const totalPages = Math.max(1, Math.ceil(total / stockState.perPage));
            if (stockState.page > totalPages) stockState.page = totalPages;
            const start = (stockState.page - 1) * stockState.perPage;
            const pageRows = rows.slice(start, start + stockState.perPage);
            const notesVisible = rows.some((movement) => Boolean(String(movement.notes || '').trim()));
            document.getElementById('notes-col-head').style.display = notesVisible ? '' : 'none';

            if (!pageRows.length) {
                const colCount = notesVisible ? 8 : 7;
                tbody.innerHTML = `<tr><td colspan="${colCount}" class="text-center text-muted py-4">Tidak ada data keluar masuk barang</td></tr>`;
            } else {
                tbody.innerHTML = pageRows.map((movement) => {
                    const movementDate = new Date(movement.created_at);
                    const formattedDate = movementDate.toLocaleDateString('id-ID', { year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit' });
                    const isIn = movement.type === 'in';
                    const typeText = isIn ? '↑ Masuk' : '↓ Keluar';
                    const badgeClass = isIn ? 'stock-type-badge stock-type-in' : 'stock-type-badge stock-type-out';
                    const qty = Number(movement.quantity || 0);
                    const before = Number(movement.stock_before ?? 0);
                    const after = Number(movement.stock_after ?? 0);
                    const deltaUnit = Math.abs(after - before).toLocaleString('id-ID');
                    const arrowIcon = isIn ? '↑' : '↓';
                    const changeText = isIn ? `bertambah ${deltaUnit} unit` : `berkurang ${deltaUnit} unit`;
                    const notesCell = notesVisible ? `<td class="text-muted small">${movement.notes || '—'}</td>` : '';
                    return `
                        <tr>
                            <td class="text-nowrap">${formattedDate}</td>
                            <td>${movement.product?.name || '—'}</td>
                            <td><span class="${badgeClass}">${typeText}</span></td>
                            <td class="text-end">${qty.toLocaleString('id-ID')}</td>
                            <td class="stock-change-cell">
                                <div class="stock-change-main">${before.toLocaleString('id-ID')} → ${after.toLocaleString('id-ID')}</div>
                                <div class="stock-change-sub ${isIn ? 'in' : 'out'}"><span>${arrowIcon}</span><span>${changeText}</span></div>
                            </td>
                            <td class="text-end"><span class="stock-delta ${isIn ? 'in' : 'out'}">${after.toLocaleString('id-ID')}</span></td>
                            ${notesCell}
                            <td class="text-nowrap">${movement.user?.name || '—'}</td>
                        </tr>
                    `;
                }).join('');
            }

            document.getElementById('stock-result-count').textContent = `${total.toLocaleString('id-ID')} hasil`;
            document.getElementById('page-indicator').textContent = `Hal. ${stockState.page}/${totalPages}`;
            document.getElementById('page-prev').disabled = stockState.page <= 1;
            document.getElementById('page-next').disabled = stockState.page >= totalPages;
        }

        async function loadStockData() {
            const tbody = document.querySelector('#stock-table tbody');
            tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted py-4">Memuat data…</td></tr>';
            try {
                const res = await fetch(buildStockQueryUrl());
                const data = await res.json();
                if (data.error) throw new Error(data.error);
                stockState.rows = Array.isArray(data.data) ? data.data : [];
                stockState.page = 1;
                const summary = data.summary || { total_movements: 0, total_in: 0, total_out: 0, net_change: 0 };
                updateKpi(summary);
                updateInsights(stockState.rows, summary);
                renderTable();
            } catch (error) {
                console.error('Error loading stock data:', error);
                stockState.rows = [];
                stockState.page = 1;
                updateKpi({ total_movements: 0, total_in: 0, total_out: 0, net_change: 0 });
                updateInsights([], { total_in: 0, total_out: 0 });
                renderTable();
            }
        }

        function parseFilenameFromDisposition(header) {
            if (!header) return null;
            const utf8 = header.match(/filename\*=UTF-8''([^;]+)/i);
            if (utf8) return decodeURIComponent(utf8[1].trim());
            const plain = header.match(/filename="?([^";]+)"?/i);
            return plain ? plain[1].trim() : null;
        }

        let stockDownloadBusy = false;

        function closeStockDownloadMenu() {
            const wrap = document.getElementById('stock-download-wrap');
            const toggle = document.getElementById('btn-stock-download-toggle');
            if (wrap) wrap.classList.remove('is-open');
            if (toggle) toggle.setAttribute('aria-expanded', 'false');
        }

        function openStockDownloadMenu() {
            const wrap = document.getElementById('stock-download-wrap');
            const toggle = document.getElementById('btn-stock-download-toggle');
            if (wrap) wrap.classList.add('is-open');
            if (toggle) toggle.setAttribute('aria-expanded', 'true');
        }

        function initStockDownloadMenu() {
            const wrap = document.getElementById('stock-download-wrap');
            const toggle = document.getElementById('btn-stock-download-toggle');
            if (!wrap || !toggle) return;

            toggle.addEventListener('click', function(e) {
                e.stopPropagation();
                if (stockDownloadBusy) return;
                if (wrap.classList.contains('is-open')) {
                    closeStockDownloadMenu();
                } else {
                    openStockDownloadMenu();
                }
            });

            document.addEventListener('click', function(e) {
                if (!wrap.contains(e.target)) closeStockDownloadMenu();
            });

            document.querySelectorAll('.stock-download-menu-item[data-stock-period]').forEach((btn) => {
                btn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const period = btn.getAttribute('data-stock-period');
                    if (period && !stockDownloadBusy) {
                        closeStockDownloadMenu();
                        downloadStockReport(period);
                    }
                });
            });
        }

        function setStockDownloadStatus(type, message) {
            const status = document.getElementById('stock-download-status');
            if (!status) return;
            status.classList.remove('is-loading', 'is-success', 'is-error');
            if (type) status.classList.add(type);
            status.textContent = message;
        }

        function setStockDownloadLoading(active, period, message) {
            const toggle = document.getElementById('btn-stock-download-toggle');
            const menuItems = document.querySelectorAll('.stock-download-menu-item[data-stock-period]');
            if (toggle) {
                toggle.disabled = active;
                if (active && !toggle.dataset.originalHtml) {
                    toggle.dataset.originalHtml = toggle.innerHTML;
                    toggle.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span>Mengunduh...</span>';
                } else if (!active && toggle.dataset.originalHtml) {
                    toggle.innerHTML = toggle.dataset.originalHtml;
                    delete toggle.dataset.originalHtml;
                }
            }
            menuItems.forEach((btn) => { btn.disabled = active; });
            if (active) {
                closeStockDownloadMenu();
                setStockDownloadStatus('is-loading', message || 'Menyiapkan file Excel, mohon tunggu...');
            }
        }

        async function downloadStockReport(period) {
            if (stockDownloadBusy) return;
            stockDownloadBusy = true;

            const periodLabel = { daily: 'harian', weekly: 'mingguan', monthly: 'bulanan' }[period] || period;
            setStockDownloadLoading(true, period, `Menyiapkan laporan ${periodLabel}...`);

            const date = getReferenceDateValue();
            const product = document.getElementById('product')?.value || '';
            const params = new URLSearchParams({ period });
            if (date) params.set('date', date);
            if (product) params.set('product_id', product);

            const url = `/stock-reports/download/excel?${params.toString()}`;
            const defaultName = `laporan_stok_${period}.xlsx`;

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
                downloadedName = parseFilenameFromDisposition(res.headers.get('Content-Disposition')) || defaultName;
                const blobUrl = URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.href = blobUrl;
                link.download = downloadedName;
                document.body.appendChild(link);
                link.click();
                link.remove();
                setTimeout(() => URL.revokeObjectURL(blobUrl), 1000);
            } catch (error) {
                console.error('Download stock report failed:', error);
                setStockDownloadStatus('is-error', error?.message || 'Gagal mengunduh laporan. Coba lagi.');
            } finally {
                stockDownloadBusy = false;
                setStockDownloadLoading(false, period);
                if (downloadedName) {
                    setStockDownloadStatus('is-success', `Berhasil mengunduh: ${downloadedName}`);
                }
            }
        }

        async function loadProducts() {
            try {
                const res = await fetch('/api/products?per_page=500');
                const data = await res.json();
                const productSelect = document.getElementById('product');
                productSelect.innerHTML = '<option value="">Semua produk</option>';
                const rows = Array.isArray(data.data) ? data.data : [];
                rows.forEach(product => {
                    const opt = document.createElement('option');
                    opt.value = product.id;
                    opt.textContent = product.name;
                    productSelect.appendChild(opt);
                });
            } catch (error) { console.error('Error loading products:', error); }
        }

        document.addEventListener('DOMContentLoaded', function() {
            initStockDownloadMenu();
            loadProducts();
            loadStockData();
            document.getElementById('btn-apply')?.addEventListener('click', loadStockData);
            document.getElementById('btn-reset')?.addEventListener('click', function() {
                document.getElementById('period').value = '';
                document.getElementById('date_start').value = '{{ date('Y-m-d') }}';
                document.getElementById('product').value = '';
                document.getElementById('type').value = '';
                loadStockData();
            });
            document.getElementById('page-prev')?.addEventListener('click', function() {
                if (stockState.page > 1) { stockState.page -= 1; renderTable(); }
            });
            document.getElementById('page-next')?.addEventListener('click', function() {
                const totalPages = Math.max(1, Math.ceil((stockState.rows || []).length / stockState.perPage));
                if (stockState.page < totalPages) { stockState.page += 1; renderTable(); }
            });
        });
    </script>
@endpush
