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

class ProductReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle
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
        return collect($this->data['products']);
    }

    public function headings(): array
    {
        return [
            'No',
            'Kode Produk',
            'Nama Produk',
            'Kategori',
            'Supplier',
            'Stok Tersedia',
            'Harga Jual',
            'Total Terjual',
            'Total Pendapatan'
        ];
    }

    public function map($product): array
    {
        static $no = 1;
        return [
            $no++,
            $product->code ?? '-',
            $product->name ?? '-',
            $product->category->name ?? '-',
            $product->supplier->name ?? '-',
            $product->stock_quantity ?? 0,
            'Rp ' . number_format($product->price ?? 0, 0, ',', '.'),
            $product->total_sold ?? 0,
            'Rp ' . number_format($product->total_revenue ?? 0, 0, ',', '.')
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
            'B' => 15,  // Kode Produk
            'C' => 30,  // Nama Produk
            'D' => 20,  // Kategori
            'E' => 20,  // Supplier
            'F' => 15,  // Stok
            'G' => 18,  // Harga Jual
            'H' => 15,  // Total Terjual
            'I' => 20,  // Total Pendapatan
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

        return "Laporan Produk {$periodText[$this->period]} - {$dateText}";
    }
}
