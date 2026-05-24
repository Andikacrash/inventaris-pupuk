<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Penjualan</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .company-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .report-title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .report-info {
            font-size: 12px;
            margin-bottom: 20px;
        }
        .summary-box {
            background-color: #f5f5f5;
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .summary-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr 1fr;
            gap: 15px;
        }
        .summary-item {
            text-align: center;
        }
        .summary-label {
            font-size: 11px;
            color: #666;
            margin-bottom: 5px;
        }
        .summary-value {
            font-size: 16px;
            font-weight: bold;
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #4472C4;
            color: white;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .footer {
            margin-top: 30px;
            text-align: right;
            font-size: 11px;
            color: #666;
        }
        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">TOKO PUPUK MAKMUR</div>
        <div class="report-title">LAPORAN PENJUALAN {{ strtoupper($periodText) }}</div>
        <div class="report-info">
            Periode: {{ $dateText }}<br>
            Tanggal Cetak: {{ now()->format('d/m/Y H:i') }}
        </div>
    </div>

    <div class="summary-box">
        <h3 style="margin-top: 0; text-align: center;">Ringkasan Penjualan</h3>
        <div class="summary-grid">
            <div class="summary-item">
                <div class="summary-label">Total Transaksi</div>
                <div class="summary-value">{{ $summary['total_sales'] }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Total Item</div>
                <div class="summary-value">{{ $summary['total_items'] }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Total Pendapatan</div>
                <div class="summary-value">Rp {{ number_format($summary['total_amount'], 0, ',', '.') }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Rata-rata Transaksi</div>
                <div class="summary-value">Rp {{ number_format($summary['average_transaction'], 0, ',', '.') }}</div>
            </div>
        </div>
    </div>

    @if(count($summary['top_products']) > 0)
    <div class="summary-box">
        <h3 style="margin-top: 0; text-align: center;">Top 5 Produk Terjual</h3>
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Produk</th>
                    <th>Quantity Terjual</th>
                    <th>Total Pendapatan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($summary['top_products'] as $index => $product)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $product['product_name'] }}</td>
                    <td>{{ $product['quantity'] }}</td>
                    <td>Rp {{ number_format($product['revenue'], 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <h3>Detail Transaksi</h3>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Invoice</th>
                <th>Kasir</th>
                <th>Total Item</th>
                <th>Total Harga</th>
                <th>Metode Bayar</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sales as $index => $sale)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $sale->created_at->format('d/m/Y H:i') }}</td>
                <td>{{ $sale->invoice_number ?? '-' }}</td>
                <td>{{ $sale->user->name ?? '-' }}</td>
                <td>{{ $sale->total_items ?? 0 }}</td>
                <td>Rp {{ number_format($sale->total_amount ?? 0, 0, ',', '.') }}</td>
                <td>{{ $sale->payment_method ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Dicetak pada: {{ now()->format('d/m/Y H:i:s') }}</p>
        <p>Oleh: {{ auth()->user()->name ?? 'System' }}</p>
    </div>
</body>
</html>
