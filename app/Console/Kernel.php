<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Generate laporan harian setiap hari jam 23:59
        $schedule->command('report:generate-daily')
                 ->dailyAt('23:59')
                 ->appendOutputTo(storage_path('logs/reports.log'));

        // Generate laporan mingguan setiap Minggu jam 23:59
        $schedule->command('report:generate-weekly')
                 ->weeklyOn(0, '23:59') // 0 = Sunday
                 ->appendOutputTo(storage_path('logs/reports.log'));

        // Generate laporan bulanan setiap akhir bulan jam 23:59
        $schedule->command('report:generate-monthly')
                 ->monthlyOn(date('t'), '23:59') // date('t') = last day of month
                 ->appendOutputTo(storage_path('logs/reports.log'));

        // Clean up old reports (hapus laporan lebih dari 1 tahun)
        $schedule->call(function () {
            $this->cleanupOldReports();
        })->monthly();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }

    /**
     * Clean up old reports
     */
    private function cleanupOldReports()
    {
        $cutoffDate = now()->subYear();
        
        // Clean daily reports older than 1 year
        $dailyFiles = Storage::files('reports/daily');
        foreach ($dailyFiles as $file) {
            if (str_contains($file, $cutoffDate->format('Y'))) {
                Storage::delete($file);
            }
        }

        // Clean weekly reports older than 1 year
        $weeklyFiles = Storage::files('reports/weekly');
        foreach ($weeklyFiles as $file) {
            if (str_contains($file, $cutoffDate->format('Y'))) {
                Storage::delete($file);
            }
        }

        // Clean monthly reports older than 1 year
        $monthlyFiles = Storage::files('reports/monthly');
        foreach ($monthlyFiles as $file) {
            if (str_contains($file, $cutoffDate->format('Y'))) {
                Storage::delete($file);
            }
        }

        Log::info('Old reports cleanup completed');
    }
}
