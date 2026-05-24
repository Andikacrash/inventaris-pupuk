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

class GenerateWeeklyReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'report:generate-weekly {--date= : Tanggal untuk laporan (format: Y-m-d)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate laporan mingguan otomatis untuk penjualan, stok, dan produk';

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
        $weekStart = $date->copy()->startOfWeek();
        $weekEnd = $date->copy()->endOfWeek();

        $this->info("Generating weekly reports for week: {$weekStart->format('Y-m-d')} to {$weekEnd->format('Y-m-d')}");

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

            $this->info('All weekly reports generated successfully!');
            return 0;
        } catch (\Exception $e) {
            $this->error('Error generating reports: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Generate laporan penjualan mingguan
     */
    private function generateSalesReport($date)
    {
        $data = $this->reportService->generateSalesReport('weekly', $date->format('Y-m-d'));

        // Generate Excel
        $excelFilename = "laporan_penjualan_mingguan_{$date->format('Y-W')}.xlsx";
        Excel::store(
            new SalesReportExport($data, 'weekly', $date->format('Y-m-d')),
            "reports/weekly/sales/{$excelFilename}"
        );

        // Generate PDF
        $pdfFilename = "laporan_penjualan_mingguan_{$date->format('Y-W')}.pdf";
        $pdf = Pdf::loadView('reports.sales-pdf', [
            'sales' => $data['sales'],
            'summary' => $data['summary'],
            'periodText' => 'Mingguan',
            'dateText' => $date->startOfWeek()->format('d/m/Y') . ' - ' . $date->endOfWeek()->format('d/m/Y')
        ]);

        Storage::put("reports/weekly/sales/{$pdfFilename}", $pdf->output());

        $this->info("Sales report saved: {$excelFilename} and {$pdfFilename}");
    }

    /**
     * Generate laporan stok mingguan
     */
    private function generateStockReport($date)
    {
        $data = $this->reportService->generateStockReport('weekly', $date->format('Y-m-d'));

        // Generate Excel
        $excelFilename = "laporan_stok_mingguan_{$date->format('Y-W')}.xlsx";
        Excel::store(
            new StockReportExport($data, 'weekly', $date->format('Y-m-d')),
            "reports/weekly/stock/{$excelFilename}"
        );

        // Generate PDF
        $pdfFilename = "laporan_stok_mingguan_{$date->format('Y-W')}.pdf";
        $pdf = Pdf::loadView('reports.stock-pdf', [
            'movements' => $data['movements'],
            'summary' => $data['summary'],
            'period' => 'weekly',
            'dateText' => $date->startOfWeek()->format('d/m/Y') . ' - ' . $date->endOfWeek()->format('d/m/Y')
        ]);

        Storage::put("reports/weekly/stock/{$pdfFilename}", $pdf->output());

        $this->info("Stock report saved: {$excelFilename} and {$pdfFilename}");
    }

    /**
     * Generate laporan produk mingguan
     */
    private function generateProductReport($date)
    {
        $data = $this->reportService->generateProductReport('weekly', $date->format('Y-m-d'));

        // Generate Excel
        $excelFilename = "laporan_produk_mingguan_{$date->format('Y-W')}.xlsx";
        Excel::store(
            new ProductReportExport($data, 'weekly', $date->format('Y-m-d')),
            "reports/weekly/products/{$excelFilename}"
        );

        $this->info("Product report saved: {$excelFilename}");
    }
}
