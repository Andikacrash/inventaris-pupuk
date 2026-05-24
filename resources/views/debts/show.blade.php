@extends('layouts.app')

@php
    $payPct = $debt->total_amount > 0 ? ($debt->paid_amount / $debt->total_amount) * 100 : 0;
    $statusClass = match ($debt->status) {
        'paid' => 'debt-detail-pill debt-detail-pill--paid',
        'partial' => 'debt-detail-pill debt-detail-pill--partial',
        default => 'debt-detail-pill debt-detail-pill--unpaid',
    };
    $statusLabel = match ($debt->status) {
        'paid' => 'Lunas',
        'partial' => 'Sebagian lunas',
        default => 'Belum lunas',
    };
    $saleItems = $debt->sale?->saleItems ?? collect();
@endphp

@push('styles')
    <style>
        .debt-detail-page {
            /* Selaras tema warm — kartu terang, teks gelap terbaca */
            --dd-surface: #ffffff;
            --dd-elevated: #f5f1eb;
            --dd-border: #c9b8a3;
            --dd-border-strong: #9a8468;
            --dd-box-shadow: 0 1px 0 rgba(60, 48, 32, 0.06), 0 2px 8px rgba(60, 48, 32, 0.07);
            --dd-text: #1e1710;
            --dd-muted: #5c4a38;
            --dd-accent: #1a5c42;
            --dd-accent-soft: rgba(26, 92, 66, 0.12);
            color: var(--dd-text) !important;
            max-width: 1200px;
            margin: 0 auto;
        }

        .debt-detail-back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--dd-muted) !important;
            text-decoration: none !important;
            font-size: 0.88rem;
            margin-bottom: 14px;
        }

        .debt-detail-back:hover {
            color: var(--dd-accent) !important;
        }

        .debt-detail-hero {
            background: var(--dd-surface);
            border: 1.5px solid var(--dd-border-strong);
            box-shadow: var(--dd-box-shadow);
            border-radius: 14px;
            padding: 18px 20px;
            margin-bottom: 16px;
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: space-between;
            align-items: flex-start;
        }

        .debt-detail-eyebrow {
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--dd-muted) !important;
            margin-bottom: 6px;
        }

        .debt-detail-progress-text {
            font-size: 0.85rem;
            color: var(--dd-muted) !important;
            margin-bottom: 14px;
        }

        .debt-detail-progress-text strong {
            color: var(--dd-text) !important;
        }

        .debt-detail-page .debt-detail-card__body,
        .debt-detail-page .text-muted {
            color: var(--dd-muted) !important;
        }

        .debt-detail-hero-side .label,
        .debt-detail-hero-side .sub,
        .debt-detail-remain-box .t,
        .debt-detail-meta {
            color: var(--dd-muted) !important;
        }

        .debt-detail-meta {
            margin: 12px 0 0;
            font-size: 0.8rem;
        }

        .debt-detail-customer {
            margin: 0 0 10px 0;
            font-size: 1.35rem;
            font-weight: 700;
            color: var(--dd-text) !important;
            line-height: 1.2;
        }

        .debt-detail-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            align-items: center;
        }

        .debt-detail-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 0.78rem;
            background: var(--dd-elevated);
            border: 1.5px solid var(--dd-border);
            color: var(--dd-text) !important;
        }

        .debt-detail-chip--invoice {
            font-family: var(--font-sans, system-ui);
            font-variant-numeric: tabular-nums;
        }

        .debt-detail-chip--due-soon {
            border-color: #f59e0b;
            background: #fef3c7;
            color: #92400e !important;
        }

        .debt-detail-chip--overdue {
            border-color: #f87171;
            background: #fee2e2;
            color: #991b1b !important;
        }

        .debt-detail-hero-side {
            text-align: right;
            min-width: 200px;
        }

        .debt-detail-hero-side .label {
            font-size: 0.75rem;
            color: var(--dd-muted);
            margin-bottom: 4px;
        }

        .debt-detail-hero-side .sisa {
            font-size: 1.5rem;
            font-weight: 700;
            color: #b91c1c !important;
            font-variant-numeric: tabular-nums;
        }

        .debt-detail-hero-side .sub {
            font-size: 0.8rem;
            color: var(--dd-muted);
            margin-top: 6px;
        }

        .debt-detail-pill {
            display: inline-block;
            font-size: 0.72rem;
            font-weight: 600;
            padding: 4px 12px;
            border-radius: 999px;
        }

        .debt-detail-pill--partial {
            background: #fef3c7;
            color: #92400e !important;
            border: 1px solid #f59e0b;
        }

        .debt-detail-pill--unpaid {
            background: #fee2e2;
            color: #991b1b !important;
            border: 1px solid #f87171;
        }

        .debt-detail-pill--paid {
            background: #dcfce7;
            color: #166534 !important;
            border: 1px solid #4ade80;
        }

        .debt-detail-card {
            background: var(--dd-surface);
            border: 1.5px solid var(--dd-border-strong);
            box-shadow: var(--dd-box-shadow);
            border-radius: 14px;
            overflow: hidden;
            margin-bottom: 14px;
        }

        .debt-detail-card__head {
            padding: 12px 16px;
            background: var(--dd-elevated);
            border-bottom: 1.5px solid var(--dd-border-strong);
            font-size: 0.82rem;
            font-weight: 600;
            color: var(--dd-text) !important;
        }

        .debt-detail-card__body {
            padding: 14px 16px;
        }

        .debt-detail-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.86rem;
        }

        .debt-detail-table th,
        .debt-detail-table td {
            padding: 10px 12px;
            text-align: left;
            border-bottom: 1px solid var(--dd-border);
        }

        .debt-detail-table th {
            color: var(--dd-muted) !important;
            font-weight: 700;
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            background: var(--dd-elevated) !important;
            border-bottom: 1.5px solid var(--dd-border-strong) !important;
        }

        .debt-detail-table tbody tr:last-child td {
            border-bottom: none;
        }

        .debt-detail-table td {
            color: var(--dd-text) !important;
        }

        .debt-detail-table .num {
            text-align: right;
            font-variant-numeric: tabular-nums;
        }

        .debt-detail-stats {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 10px;
            margin-bottom: 14px;
        }

        @media (max-width: 576px) {
            .debt-detail-stats {
                grid-template-columns: 1fr;
            }

            .debt-detail-hero-side {
                text-align: left;
                width: 100%;
            }
        }

        .debt-detail-stat {
            background: var(--dd-surface);
            border: 1.5px solid var(--dd-border-strong);
            box-shadow: var(--dd-box-shadow);
            border-radius: 12px;
            padding: 12px 14px;
        }

        .debt-detail-stat .k {
            font-size: 0.7rem;
            color: var(--dd-muted) !important;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 6px;
        }

        .debt-detail-stat .v {
            font-size: 1.05rem;
            font-weight: 600;
            font-variant-numeric: tabular-nums;
            color: var(--dd-text) !important;
        }

        .debt-detail-stat .v--ok {
            color: #166534 !important;
        }

        .debt-detail-stat .v--danger {
            color: #b91c1c !important;
        }

        .debt-detail-progress {
            height: 8px;
            border-radius: 999px;
            background: #e8e0d5;
            overflow: hidden;
            margin-top: 8px;
        }

        .debt-detail-progress > span {
            display: block;
            height: 100%;
            border-radius: 999px;
            background: linear-gradient(90deg, #40916c, #34d399);
        }

        .debt-detail-pay-panel {
            background: var(--dd-surface);
            border: 1.5px solid var(--dd-border-strong);
            box-shadow: var(--dd-box-shadow);
            border-radius: 14px;
            padding: 18px 16px;
            position: sticky;
            top: 80px;
        }

        .debt-detail-pay-panel h3 {
            margin: 0 0 14px 0;
            font-size: 0.82rem;
            font-weight: 600;
            color: var(--dd-text) !important;
        }

        .debt-detail-remain-box {
            background: var(--dd-elevated);
            border: 1.5px solid var(--dd-border-strong);
            border-radius: 12px;
            padding: 14px;
            margin-bottom: 16px;
        }

        .debt-detail-remain-box .t {
            font-size: 0.75rem;
            color: var(--dd-muted);
            margin-bottom: 4px;
        }

        .debt-detail-remain-box .amt {
            font-size: 1.35rem;
            font-weight: 700;
            color: #b45309 !important;
            font-variant-numeric: tabular-nums;
        }

        .debt-detail-form-label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--dd-muted) !important;
            margin-bottom: 6px;
        }

        .debt-detail-input,
        .debt-detail-select,
        .debt-detail-textarea {
            width: 100%;
            padding: 10px 12px;
            border-radius: 10px;
            border: 1px solid var(--dd-border) !important;
            background: #ffffff !important;
            color: var(--dd-text) !important;
            font-size: 0.92rem;
        }

        .debt-detail-input:focus,
        .debt-detail-select:focus,
        .debt-detail-textarea:focus {
            outline: none;
            border-color: var(--dd-accent);
            box-shadow: 0 0 0 3px var(--dd-accent-soft);
        }

        .debt-detail-input-group {
            display: flex;
            align-items: stretch;
            border-radius: 10px;
            overflow: hidden;
            border: 1px solid var(--dd-border);
        }

        .debt-detail-input-group span {
            padding: 10px 12px;
            background: var(--dd-elevated);
            color: var(--dd-muted) !important;
            font-size: 0.88rem;
            border-right: 1px solid var(--dd-border);
        }

        .debt-detail-input-group input {
            border: none !important;
            border-radius: 0 !important;
            flex: 1;
        }

        .debt-detail-hint {
            font-size: 0.75rem;
            color: var(--dd-muted) !important;
            margin-top: 6px;
        }

        .debt-detail-quick {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 14px;
        }

        .debt-detail-quick button {
            padding: 6px 12px;
            border-radius: 999px;
            font-size: 0.78rem;
            font-weight: 600;
            cursor: pointer;
            border: 1px solid var(--dd-border);
            background: var(--dd-elevated);
            color: var(--dd-text) !important;
        }

        .debt-detail-quick button:hover {
            background: #ebe4d8;
        }

        .debt-detail-quick button.is-primary {
            background: var(--dd-accent);
            border-color: var(--dd-accent);
            color: #fff !important;
        }

        .debt-detail-submit {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 10px;
            background: var(--dd-accent);
            color: #fff;
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
            margin-top: 4px;
        }

        .debt-detail-submit:hover {
            background: #2f6f52;
        }

        .debt-detail-done {
            text-align: center;
            padding: 28px 16px;
        }

        .debt-detail-done i {
            color: #34d399;
            font-size: 2.5rem;
            margin-bottom: 12px;
        }

        body.debts-page .debt-detail-page .alert-success {
            background: rgba(52, 211, 153, 0.12);
            border: 1px solid rgba(52, 211, 153, 0.35);
            color: #166534 !important;
            border-radius: 10px;
        }

        body.debts-page .debt-detail-page .alert-danger {
            background: rgba(248, 113, 113, 0.12);
            border: 1px solid rgba(248, 113, 113, 0.35);
            color: #991b1b !important;
            border-radius: 10px;
        }

        /* Timpa label global tema warm (warna gelap di kartu terang) */
        body.ps-theme-warm.debts-page .debt-detail-page label.debt-detail-form-label {
            color: var(--dd-muted) !important;
        }
    </style>
@endpush

@section('content')
    <div class="debt-detail-page">
        <a href="{{ route('debts.index', ['view' => 'grouped']) }}" class="debt-detail-back">
            <i class="fas fa-arrow-left"></i> Kembali ke manajemen hutang
        </a>

        @if (session('success'))
            <div class="alert alert-success py-2 px-3 mb-3">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger py-2 px-3 mb-3">
                @foreach ($errors->all() as $err)
                    <div>{{ $err }}</div>
                @endforeach
            </div>
        @endif

        <div class="debt-detail-hero">
            <div style="flex: 1; min-width: 0;">
                <div class="debt-detail-eyebrow">Piutang penjualan</div>
                <h1 class="debt-detail-customer">{{ $debt->customer_name }}</h1>
                <div class="debt-detail-chips">
                    <span class="debt-detail-chip debt-detail-chip--invoice">
                        <i class="fas fa-file-invoice" style="opacity:0.7"></i>
                        {{ $debt->sale->invoice_number ?? '—' }}
                    </span>
                    @if ($debt->customer_phone)
                        <span class="debt-detail-chip"><i class="fas fa-phone" style="opacity:0.7"></i>
                            {{ $debt->customer_phone }}</span>
                    @endif
                    @if ($debt->due_date)
                        <span
                            class="debt-detail-chip {{ $debt->isOverdue() ? 'debt-detail-chip--overdue' : ($debt->due_date->isFuture() ? 'debt-detail-chip--due-soon' : '') }}">
                            <i class="fas fa-calendar-day" style="opacity:0.7"></i>
                            Jatuh tempo {{ $debt->due_date->format('d/m/Y') }}
                            @if ($debt->isOverdue())
                                · Terlambat
                            @endif
                        </span>
                    @else
                        <span class="debt-detail-chip">Jatuh tempo belum diatur</span>
                    @endif
                    <span class="{{ $statusClass }}">{{ $statusLabel }}</span>
                </div>
                <p class="debt-detail-meta">
                    Dicatat {{ $debt->created_at->format('d/m/Y H:i') }}
                    @if ($debt->user)
                        · {{ $debt->user->name }}
                    @endif
                </p>
            </div>
            <div class="debt-detail-hero-side">
                <div class="label">Sisa hutang</div>
                <div class="sisa">Rp {{ number_format($debt->remaining_amount, 0, ',', '.') }}</div>
                <div class="sub">
                    Total transaksi Rp {{ number_format($debt->total_amount, 0, ',', '.') }}
                    · Sudah dibayar Rp {{ number_format($debt->paid_amount, 0, ',', '.') }}
                </div>
                <div class="debt-detail-progress" aria-hidden="true">
                    <span style="width: {{ min(100, $payPct) }}%"></span>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-lg-7">
                <div class="debt-detail-card">
                    <div class="debt-detail-card__head">Rincian barang ({{ $saleItems->count() }} jenis)</div>
                    @if ($saleItems->isNotEmpty())
                        <div class="table-responsive">
                            <table class="debt-detail-table mb-0">
                                <thead>
                                    <tr>
                                        <th>Produk</th>
                                        <th class="num">Qty</th>
                                        <th class="num">Harga</th>
                                        <th class="num">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($saleItems as $line)
                                        <tr>
                                            <td>{{ $line->product->name ?? 'Produk #' . $line->product_id }}</td>
                                            <td class="num">{{ number_format($line->quantity, 0, ',', '.') }}</td>
                                            <td class="num">Rp {{ number_format($line->unit_price, 0, ',', '.') }}</td>
                                            <td class="num fw-semibold">Rp
                                                {{ number_format($line->subtotal, 0, ',', '.') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="debt-detail-card__body text-muted" style="color: var(--dd-muted) !important;">
                            Tidak ada rincian item (data penjualan mungkin diarsipkan).
                        </div>
                    @endif
                </div>

                <div class="debt-detail-stats">
                    <div class="debt-detail-stat">
                        <div class="k">Total</div>
                        <div class="v">Rp {{ number_format($debt->total_amount, 0, ',', '.') }}</div>
                    </div>
                    <div class="debt-detail-stat">
                        <div class="k">Dibayar</div>
                        <div class="v v--ok">Rp {{ number_format($debt->paid_amount, 0, ',', '.') }}</div>
                    </div>
                    <div class="debt-detail-stat">
                        <div class="k">Sisa</div>
                        <div class="v v--danger">Rp {{ number_format($debt->remaining_amount, 0, ',', '.') }}</div>
                    </div>
                </div>
                <div class="debt-detail-progress-text">
                    Progress pelunasan: <strong>{{ number_format($payPct, 1) }}%</strong>
                </div>

                @php
                    $activePlan = $debt->activeInstallmentPlan;
                    $installmentRows = $activePlan?->installmentPayments?->sortBy('installment_number') ?? collect();
                @endphp

                <div class="debt-detail-card">
                    <div class="debt-detail-card__head d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <span>Rencana cicilan</span>
                        @if ($activePlan)
                            <span class="debt-detail-pill debt-detail-pill--partial" style="font-size:0.75rem;">
                                {{ $activePlan->paid_count }}/{{ $activePlan->installment_count }} angsuran
                            </span>
                        @endif
                    </div>
                    @if ($activePlan && $installmentRows->isNotEmpty())
                        <div class="debt-detail-card__body" style="padding: 0 0 12px;">
                            <p class="debt-detail-meta px-3 pt-2 mb-2">
                                {{ ucfirst($activePlan->frequency) }} · Rp {{ number_format($activePlan->installment_amount, 0, ',', '.') }}/angsuran
                            </p>
                            <div class="table-responsive">
                                <table class="debt-detail-table mb-0">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Jatuh tempo</th>
                                            <th class="num">Nominal</th>
                                            <th>Status</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($installmentRows as $inst)
                                            <tr>
                                                <td>{{ $inst->installment_number }}</td>
                                                <td class="text-nowrap">{{ $inst->due_date->format('d/m/Y') }}</td>
                                                <td class="num">Rp {{ number_format($inst->amount, 0, ',', '.') }}</td>
                                                <td>
                                                    @if ($inst->status === 'paid')
                                                        <span class="debt-detail-pill debt-detail-pill--paid">Lunas</span>
                                                    @elseif ($inst->status === 'overdue')
                                                        <span class="debt-detail-pill debt-detail-pill--unpaid">Terlambat</span>
                                                    @else
                                                        <span class="debt-detail-pill debt-detail-pill--partial">Menunggu</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($inst->status !== 'paid')
                                                        <form action="{{ route('installments.pay', $inst) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            <input type="hidden" name="payment_date" value="{{ date('Y-m-d') }}">
                                                            <input type="hidden" name="payment_method" value="cash">
                                                            <button type="submit" class="btn btn-sm btn-outline-success" style="font-size:0.75rem;">
                                                                Bayar
                                                            </button>
                                                        </form>
                                                    @else
                                                        <span class="text-muted small">{{ $inst->payment_date?->format('d/m/Y') ?? '—' }}</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @elseif ($debt->status != 'paid')
                        <div class="debt-detail-card__body">
                            <p class="debt-detail-meta mb-3">Belum ada rencana cicilan. Buat jadwal angsuran di bawah.</p>
                            <form action="{{ route('installments.create-plan', $debt) }}" method="POST" class="row g-2">
                                @csrf
                                <div class="col-6">
                                    <label class="debt-detail-form-label">Jumlah angsuran</label>
                                    <input type="number" name="installment_count" class="debt-detail-input" min="2" max="12" value="4" required>
                                </div>
                                <div class="col-6">
                                    <label class="debt-detail-form-label">Nominal/angsuran (Rp)</label>
                                    <input type="number" name="installment_amount" class="debt-detail-input" step="0.01" min="0.01"
                                        max="{{ $debt->remaining_amount }}"
                                        value="{{ round($debt->remaining_amount / 4, 0) }}" required>
                                </div>
                                <div class="col-6">
                                    <label class="debt-detail-form-label">Frekuensi</label>
                                    <select name="frequency" class="debt-detail-select" required>
                                        <option value="monthly" selected>Bulanan</option>
                                        <option value="weekly">Mingguan</option>
                                        <option value="daily">Harian</option>
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label class="debt-detail-form-label">Mulai tanggal</label>
                                    <input type="date" name="start_date" class="debt-detail-input" value="{{ date('Y-m-d') }}" required>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="debt-detail-submit">
                                        <i class="fas fa-calendar-alt me-2"></i>Buat rencana cicilan
                                    </button>
                                </div>
                            </form>
                        </div>
                    @else
                        <div class="debt-detail-card__body text-muted">Tidak ada rencana cicilan aktif.</div>
                    @endif
                </div>

                <div class="debt-detail-card">
                    <div class="debt-detail-card__head">Riwayat pembayaran</div>
                    @if ($debt->payments && $debt->payments->count() > 0)
                        <div class="table-responsive">
                            <table class="debt-detail-table mb-0">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th class="num">Jumlah</th>
                                        <th>Metode</th>
                                        <th>Oleh</th>
                                        <th>Catatan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($debt->payments as $payment)
                                        <tr>
                                            <td class="text-nowrap">{{ $payment->payment_date->format('d/m/Y') }}</td>
                                            <td class="num fw-semibold">Rp
                                                {{ number_format($payment->amount, 0, ',', '.') }}</td>
                                            <td>{{ ucfirst($payment->payment_method) }}</td>
                                            <td>{{ $payment->user->name ?? '—' }}</td>
                                            <td style="color: var(--dd-muted); font-size: 0.82rem;">
                                                {{ $payment->notes ?: '—' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="debt-detail-card__body" style="color: var(--dd-muted);">Belum ada riwayat pembayaran.
                        </div>
                    @endif
                </div>
            </div>

            <div class="col-lg-5">
                @if ($debt->status != 'paid')
                    <div class="debt-detail-pay-panel">
                        <h3>Catat pembayaran</h3>
                        <div class="debt-detail-remain-box">
                            <div class="t">Maksimal nominal sekarang</div>
                            <div class="amt">Rp {{ number_format($debt->remaining_amount, 0, ',', '.') }}</div>
                        </div>

                        <form action="{{ route('debts.record-payment', $debt) }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="debt-detail-form-label" for="payment-amount">Jumlah pembayaran</label>
                                <div class="debt-detail-input-group">
                                    <span>Rp</span>
                                    <input type="number" name="amount" id="payment-amount" class="debt-detail-input"
                                        step="0.01" min="0.01" max="{{ $debt->remaining_amount }}"
                                        value="{{ $debt->remaining_amount }}" required>
                                </div>
                                <div class="debt-detail-hint">Tidak boleh melebihi sisa hutang.</div>
                            </div>

                            <div class="debt-detail-quick">
                                <button type="button"
                                    onclick="setPaymentAmount({{ round($debt->remaining_amount * 0.25, 2) }})">25%</button>
                                <button type="button"
                                    onclick="setPaymentAmount({{ round($debt->remaining_amount * 0.5, 2) }})">50%</button>
                                <button type="button"
                                    onclick="setPaymentAmount({{ round($debt->remaining_amount * 0.75, 2) }})">75%</button>
                                <button type="button" class="is-primary"
                                    onclick="setPaymentAmount({{ $debt->remaining_amount }})">Lunas</button>
                            </div>

                            <div class="mb-3">
                                <label class="debt-detail-form-label" for="payment_date">Tanggal pembayaran</label>
                                <input type="date" name="payment_date" id="payment_date" class="debt-detail-input"
                                    value="{{ date('Y-m-d') }}" required>
                            </div>

                            <div class="mb-3">
                                <label class="debt-detail-form-label" for="payment_method">Metode</label>
                                <select name="payment_method" id="payment_method" class="debt-detail-select" required>
                                    <option value="cash">Tunai</option>
                                    <option value="transfer">Transfer</option>
                                    <option value="card">Kartu</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="debt-detail-form-label" for="notes">Catatan (opsional)</label>
                                <textarea name="notes" id="notes" class="debt-detail-textarea" rows="2"
                                    placeholder="Contoh: TF ke rek. BCA"></textarea>
                            </div>

                            <button type="submit" class="debt-detail-submit">
                                <i class="fas fa-check me-2"></i>Simpan pembayaran
                            </button>
                        </form>
                    </div>
                @else
                    <div class="debt-detail-pay-panel debt-detail-done">
                        <i class="fas fa-check-circle d-block"></i>
                        <h3 style="font-size: 1.1rem; margin: 0 0 8px;">Hutang lunas</h3>
                        <p class="debt-detail-meta" style="margin: 0; font-size: 0.9rem;">Semua pembayaran tercatat.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function setPaymentAmount(amount) {
            const input = document.getElementById('payment-amount');
            if (!input) return;
            const max = parseFloat(input.getAttribute('max')) || 0;
            let v = Number(amount);
            if (Number.isNaN(v)) v = 0;
            v = Math.min(max, Math.max(0, v));
            input.value = Math.round(v * 100) / 100;
            input.focus();
        }
    </script>
@endpush
