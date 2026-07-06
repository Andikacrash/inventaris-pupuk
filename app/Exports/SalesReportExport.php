<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Carbon\Carbon;

class SalesReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle
{
    protected $data;
    protected $period;
    protected $date;

    public function __construct($data, $period, $date = null)
    {
        $this->data = $data;
        $this->period = $period;
        $this->date = $date;
    }

    public function collection()
    {
        return collect($this->data['sales']);
    }

    public function headings(): array
    {
        return [
            'No',
            'Tanggal Transaksi',
            'No Invoice',
            'Kasir',
            'Nama Pembeli',
            'Total Item',
            'Total Harga',
            'Pembayaran',
            'Kembalian',
            'Metode Pembayaran',
            'Status'
        ];
    }

    public function map($sale): array
    {
        static $no = 1;
        $saleDate = $sale->sale_date ?? $sale->created_at;
        $formattedDate = $saleDate instanceof \DateTimeInterface
            ? Carbon::instance($saleDate)->format('d/m/Y H:i')
            : Carbon::parse($saleDate)->format('d/m/Y H:i');

        return [
            $no++,
            $formattedDate,
            $sale->invoice_number ?? '-',
            $sale->user->name ?? '-',
            $sale->customer_name ?? '-',
            $sale->total_items ?? 0,
            'Rp '.number_format($sale->total_amount ?? 0, 0, ',', '.'),
            'Rp '.number_format($sale->payment_amount ?? 0, 0, ',', '.'),
            'Rp '.number_format($sale->change_amount ?? 0, 0, ',', '.'),
            $sale->payment_method ?? '-',
            $sale->status ?? 'Completed',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Header styling
        $sheet->getStyle('A1:K1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Border untuk semua data
        $lastRow = $sheet->getHighestRow();
        $sheet->getStyle("A1:K{$lastRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        // Auto-filter
        $sheet->setAutoFilter("A1:K{$lastRow}");

        return $sheet;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,   // No
            'B' => 20,  // Tanggal
            'C' => 15,  // Invoice
            'D' => 20,  // Kasir
            'E' => 20,  // Nama Pembeli
            'F' => 12,  // Total Item
            'G' => 18,  // Total Harga
            'H' => 18,  // Pembayaran
            'I' => 18,  // Kembalian
            'J' => 18,  // Metode Pembayaran
            'K' => 15,  // Status
        ];
    }

    public function title(): string
    {
        $periodText = [
            'daily' => 'Harian',
            'weekly' => 'Mingguan',
            'monthly' => 'Bulanan',
        ];

        $startDate = $this->data['filters']['start_date'] ?? null;
        $endDate = $this->data['filters']['end_date'] ?? null;

        if ($startDate && $endDate && ! $this->period) {
            return 'Laporan Penjualan '.Carbon::parse($startDate)->format('d/m/Y')
                .' - '.Carbon::parse($endDate)->format('d/m/Y');
        }

        if (! $this->period) {
            return 'Laporan Penjualan - '.Carbon::now()->format('d/m/Y');
        }

        $dateText = $this->date ? Carbon::parse($this->date)->format('d/m/Y') : Carbon::now()->format('d/m/Y');

        return "Laporan Penjualan {$periodText[$this->period]} - {$dateText}";
    }
}
