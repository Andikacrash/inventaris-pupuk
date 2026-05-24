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

class GenerateMonthlyReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'report:generate-monthly {--date= : Tanggal untuk laporan (format: Y-m)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate laporan bulanan otomatis untuk penjualan, stok, dan produk';

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
        $date = $this->option('date') ? Carbon::parse($this->option('date') . '-01') : Carbon::now();
        $monthStart = $date->copy()->startOfMonth();
        $monthEnd = $date->copy()->endOfMonth();
        
        $this->info("Generating monthly reports for month: {$monthStart->format('F Y')} ({$monthStart->format('Y-m-d')} to {$monthEnd->format('Y-m-d')})");
        
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
            
            $this->info('All monthly reports generated successfully!');
            return 0;
            
        } catch (\Exception $e) {
            $this->error('Error generating reports: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Generate laporan penjualan bulanan
     */
    private function generateSalesReport($date)
    {
        $data = $this->reportService->generateSalesReport('monthly', $date->format('Y-m'));
        
        // Generate Excel
        $excelFilename = "laporan_penjualan_bulanan_{$date->format('Y-m')}.xlsx";
        Excel::store(new SalesReportExport($data, 'monthly', $date->format('Y-m')), 
            "reports/monthly/sales/{$excelFilename}");
        
        // Generate PDF
        $pdfFilename = "laporan_penjualan_bulanan_{$date->format('Y-m')}.pdf";
        $pdf = Pdf::loadView('reports.sales-pdf', [
            'sales' => $data['sales'],
            'summary' => $data['summary'],
            'periodText' => 'Bulanan',
            'dateText' => $date->startOfMonth()->format('d/m/Y') . ' - ' . $date->endOfMonth()->format('d/m/Y')
        ]);
        
        Storage::put("reports/monthly/sales/{$pdfFilename}", $pdf->output());
        
        $this->info("Sales report saved: {$excelFilename} and {$pdfFilename}");
    }

    /**
     * Generate laporan stok bulanan
     */
    private function generateStockReport($date)
    {
        $data = $this->reportService->generateStockReport('monthly', $date->format('Y-m'));
        
        // Generate Excel
        $excelFilename = "laporan_stok_bulanan_{$date->format('Y-m')}.xlsx";
        Excel::store(new StockReportExport($data, 'monthly', $date->format('Y-m')), 
            "reports/monthly/stock/{$excelFilename}");
        
        // Generate PDF
        $pdfFilename = "laporan_stok_bulanan_{$date->format('Y-m')}.pdf";
        $pdf = Pdf::loadView('reports.stock-pdf', [
            'movements' => $data['movements'],
            'summary' => $data['summary'],
            'period' => 'monthly',
            'dateText' => $date->startOfMonth()->format('d/m/Y') . ' - ' . $date->endOfMonth()->format('d/m/Y')
        ]);
        
        Storage::put("reports/monthly/stock/{$pdfFilename}", $pdf->output());
        
        $this->info("Stock report saved: {$excelFilename} and {$pdfFilename}");
    }

    /**
     * Generate laporan produk bulanan
     */
    private function generateProductReport($date)
    {
        $data = $this->reportService->generateProductReport('monthly', $date->format('Y-m'));
        
        // Generate Excel
        $excelFilename = "laporan_produk_bulanan_{$date->format('Y-m')}.xlsx";
        Excel::store(new ProductReportExport($data, 'monthly', $date->format('Y-m')), 
            "reports/monthly/products/{$excelFilename}");
        
        $this->info("Product report saved: {$excelFilename}");
    }
}

