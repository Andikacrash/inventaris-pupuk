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

class StockReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle
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
        return collect($this->data['movements']);
    }

    public function headings(): array
    {
        return [
            'No',
            'Tanggal',
            'Produk',
            'Tipe',
            'Quantity',
            'Stok Sebelum',
            'Stok Sesudah',
            'Keterangan',
            'User'
        ];
    }

    public function map($movement): array
    {
        static $no = 1;
        return [
            $no++,
            $movement->created_at->format('d/m/Y H:i'),
            $movement->product_name ?? '-',
            ucfirst($movement->type ?? '-'),
            $movement->quantity ?? 0,
            $movement->stock_before ?? 0,
            $movement->stock_after ?? 0,
            $movement->notes ?? '-',
            $movement->user->name ?? '-'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Header styling
        $sheet->getStyle('A1:I1')->applyFromArray([
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
        $sheet->getStyle("A1:I{$lastRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        // Auto-filter
        $sheet->setAutoFilter("A1:I{$lastRow}");

        return $sheet;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,   // No
            'B' => 18,  // Tanggal
            'C' => 25,  // Produk
            'D' => 10,  // Tipe
            'E' => 12,  // Quantity
            'F' => 15,  // Stok Sebelum
            'G' => 15,  // Stok Sesudah
            'H' => 25,  // Keterangan
            'I' => 20,  // User
        ];
    }

    public function title(): string
    {
        $periodText = [
            'daily' => 'Harian',
            'weekly' => 'Mingguan',
            'monthly' => 'Bulanan'
        ];

        $dateText = $this->date ? Carbon::parse($this->date)->format('d/m/Y') : Carbon::now()->format('d/m/Y');

        return "Laporan Stok {$periodText[$this->period]} - {$dateText}";
    }
}
