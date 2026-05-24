<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ReportService;
use App\Exports\SalesReportExport;
use App\Exports\StockReportExport;
use App\Exports\ProductReportExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class GenerateDailyReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'report:generate-daily {--date= : Tanggal untuk laporan (format: Y-m-d)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate laporan harian otomatis untuk penjualan, stok, dan produk';

    protected $reportService;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(ReportService $reportService)
    {
        parent::__construct();
        $this->reportService = $reportService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $date = $this->option('date') ? Carbon::parse($this->option('date')) : Carbon::now();
        $dateString = $date->format('Y-m-d');

        $this->info("Generating daily reports for date: {$dateString}");

        try {
            // Generate laporan penjualan
            $this->info('Generating sales report...');
            $this->generateSalesReport($date);

            // Generate laporan stok
            $this->info('Generating stock report...');
            $this->generateStockReport($date);

            // Generate laporan produk
            $this->info('Generating product report...');
            $this->generateProductReport($date);

            $this->info('All daily reports generated successfully!');
            return 0;

        } catch (\Exception $e) {
            $this->error('Error generating reports: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Generate laporan penjualan harian
     */
    private function generateSalesReport($date)
    {
        $data = $this->reportService->generateSalesReport('daily', $date->format('Y-m-d'));

        // Generate Excel
        $excelFilename = "laporan_penjualan_harian_{$date->format('Y-m-d')}.xlsx";
        Excel::store(new SalesReportExport($data, 'daily', $date->format('Y-m-d')),
            "reports/daily/sales/{$excelFilename}");

        // Generate PDF
        $pdfFilename = "laporan_penjualan_harian_{$date->format('Y-m-d')}.pdf";
        $pdf = Pdf::loadView('reports.sales-pdf', [
            'sales' => $data['sales'],
            'summary' => $data['summary'],
            'periodText' => 'Harian',
            'dateText' => $date->format('d/m/Y')
        ]);

        Storage::put("reports/daily/sales/{$pdfFilename}", $pdf->output());

        $this->info("Sales report saved: {$excelFilename} and {$pdfFilename}");
    }

    /**
     * Generate laporan stok harian
     */
    private function generateStockReport($date)
    {
        $data = $this->reportService->generateStockReport('daily', $date->format('Y-m-d'));

        // Generate Excel
        $excelFilename = "laporan_stok_harian_{$date->format('Y-m-d')}.xlsx";
        Excel::store(new StockReportExport($data, 'daily', $date->format('Y-m-d')),
            "reports/daily/stock/{$excelFilename}");

        // Generate PDF
        $pdfFilename = "laporan_stok_harian_{$date->format('Y-m-d')}.pdf";
        $pdf = Pdf::loadView('reports.stock-pdf', [
            'movements' => $data['movements'],
            'summary' => $data['summary'],
            'period' => 'daily',
            'dateText' => $date->format('d/m/Y')
        ]);

        Storage::put("reports/daily/stock/{$pdfFilename}", $pdf->output());

        $this->info("Stock report saved: {$excelFilename} and {$pdfFilename}");
    }

    /**
     * Generate laporan produk harian
     */
    private function generateProductReport($date)
    {
        $data = $this->reportService->generateProductReport('daily', $date->format('Y-m-d'));

        // Generate Excel
        $excelFilename = "laporan_produk_harian_{$date->format('Y-m-d')}.xlsx";
        Excel::store(new ProductReportExport($data, 'daily', $date->format('Y-m-d')),
            "reports/daily/products/{$excelFilename}");

        $this->info("Product report saved: {$excelFilename}");
    }
}
