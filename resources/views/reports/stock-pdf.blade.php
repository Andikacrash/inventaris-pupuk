<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Stok</title>
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
        .summary-table {
            width: 100%;
            margin-bottom: 10px;
        }
        .summary-table td {
            text-align: center;
            width: 25%;
            border: none;
            padding: 8px;
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
        .type-in {
            color: #28a745;
            font-weight: bold;
        }
        .type-out {
            color: #dc3545;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">TOKO PUPUK MAKMUR</div>
        <div class="report-title">LAPORAN PERGERAKAN STOK {{ strtoupper($period) }}</div>
        <div class="report-info">
            Periode: {{ $dateText }}<br>
            Tanggal Cetak: {{ now()->format('d/m/Y H:i') }}
        </div>
    </div>

    <div class="summary-box">
        <h3 style="margin-top: 0; text-align: center;">Ringkasan Pergerakan Stok</h3>
        <table class="summary-table">
            <tr>
                <td>
                    <div class="summary-label">Total Masuk</div>
                    <div class="summary-value">{{ number_format($summary['total_in'] ?? 0, 0, ',', '.') }}</div>
                </td>
                <td>
                    <div class="summary-label">Total Keluar</div>
                    <div class="summary-value">{{ number_format($summary['total_out'] ?? 0, 0, ',', '.') }}</div>
                </td>
                <td>
                    <div class="summary-label">Perubahan Net</div>
                    <div class="summary-value">{{ number_format($summary['net_change'] ?? 0, 0, ',', '.') }}</div>
                </td>
                <td>
                    <div class="summary-label">Total Pergerakan</div>
                    <div class="summary-value">{{ number_format($summary['total_movements'] ?? 0, 0, ',', '.') }}</div>
                </td>
            </tr>
        </table>
    </div>

    <h3>Detail Pergerakan Stok</h3>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Produk</th>
                <th>Tipe</th>
                <th>Quantity</th>
                <th>Stok Sebelum</th>
                <th>Stok Sesudah</th>
                <th>Keterangan</th>
                <th>User</th>
            </tr>
        </thead>
        <tbody>
            @foreach($movements as $index => $movement)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $movement->created_at->format('d/m/Y H:i') }}</td>
                <td>{{ $movement->product_name ?? $movement->product?->name ?? '-' }}</td>
                <td class="type-{{ $movement->type }}">
                    {{ $movement->type === 'in' ? 'Masuk' : ($movement->type === 'out' ? 'Keluar' : '-') }}
                </td>
                <td>{{ $movement->quantity ?? 0 }}</td>
                <td>{{ $movement->stock_before ?? 0 }}</td>
                <td>{{ $movement->stock_after ?? 0 }}</td>
                <td>{{ $movement->notes ?? '-' }}</td>
                <td>{{ $movement->user?->name ?? '-' }}</td>
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
