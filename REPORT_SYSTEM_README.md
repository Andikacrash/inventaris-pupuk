# Sistem Laporan Otomatis - Inventaris Pupuk

## Overview
Sistem ini menyediakan fitur pembuatan laporan otomatis untuk inventaris pupuk dengan format PDF dan Excel yang dapat diunduh kapan saja. Laporan tersedia dalam 3 periode: harian, mingguan, dan bulanan.

## Fitur Utama

### 1. Jenis Laporan
- **Laporan Penjualan**: Transaksi penjualan dengan ringkasan dan detail
- **Laporan Stok**: Pergerakan stok (masuk/keluar) dengan ringkasan
- **Laporan Produk**: Data produk dengan performa penjualan

### 2. Format Laporan
- **PDF**: Untuk cetak dan arsip (menggunakan DomPDF)
- **Excel**: Untuk analisis lanjutan (menggunakan Laravel Excel)

### 3. Periode Laporan
- **Harian**: Data tanggal tertentu (00:00-23:59)
- **Mingguan**: Data 7 hari (Senin-Minggu)
- **Bulanan**: Data 1-akhir bulan

## Instalasi dan Setup

### 1. Install Dependencies
```bash
composer require barryvdh/laravel-dompdf maatwebsite/excel
```

### 2. Publish Config (Opsional)
```bash
php artisan vendor:publish --provider="Barryvdh\DomPDF\ServiceProvider"
php artisan vendor:publish --provider="Maatwebsite\Excel\ExcelServiceProvider"
```

### 3. Setup Storage
```bash
php artisan storage:link
```

## Penggunaan

### 1. Download Laporan via API

#### Laporan Penjualan
```bash
# PDF
GET /api/reports/download/sales/pdf?period=daily&date=2024-01-15

# Excel
GET /api/reports/download/sales/excel?period=weekly&date=2024-01-15
```

#### Laporan Stok
```bash
# PDF
GET /api/reports/download/stock/pdf?period=monthly&date=2024-01

# Excel
GET /api/reports/download/stock/excel?period=daily&date=2024-01-15
```

#### Laporan Produk
```bash
# Excel
GET /api/reports/download/product/excel?period=monthly&date=2024-01
```

### 2. Parameter Query
- `period`: `daily`, `weekly`, `monthly` (required)
- `date`: Format `Y-m-d` untuk daily/weekly, `Y-m` untuk monthly
- `user_id`: Filter berdasarkan kasir
- `payment_method`: Filter metode pembayaran
- `category_id`: Filter kategori produk
- `product_id`: Filter produk tertentu (untuk laporan stok)

### 3. Generate Laporan Otomatis

#### Manual Command
```bash
# Laporan harian
php artisan report:generate-daily --date=2024-01-15

# Laporan mingguan
php artisan report:generate-weekly --date=2024-01-15

# Laporan bulanan
php artisan report:generate-monthly --date=2024-01
```

#### Otomatis via Scheduler
Laporan akan di-generate otomatis:
- **Harian**: Setiap hari jam 23:59
- **Mingguan**: Setiap Minggu jam 23:59
- **Bulanan**: Setiap akhir bulan jam 23:59

Setup cron job:
```bash
* * * * * cd /path/to/your/project && php artisan schedule:run >> /dev/null 2>&1
```

## Struktur File

### 1. Service Layer
- `app/Services/ReportService.php` - Logika bisnis laporan

### 2. Export Classes
- `app/Exports/SalesReportExport.php` - Export Excel penjualan
- `app/Exports/StockReportExport.php` - Export Excel stok
- `app/Exports/ProductReportExport.php` - Export Excel produk

### 3. Views
- `resources/views/reports/sales-pdf.blade.php` - Template PDF penjualan
- `resources/views/reports/stock-pdf.blade.php` - Template PDF stok

### 4. Commands
- `app/Console/Commands/GenerateDailyReport.php`
- `app/Console/Commands/GenerateWeeklyReport.php`
- `app/Console/Commands/GenerateMonthlyReport.php`

### 5. Controller
- `app/Http/Controllers/Api/ReportController.php` - API endpoints

## Konfigurasi

### 1. Storage Path
Laporan otomatis disimpan di:
```
storage/app/reports/
├── daily/
│   ├── sales/
│   ├── stock/
│   └── products/
├── weekly/
│   ├── sales/
│   ├── stock/
│   └── products/
└── monthly/
    ├── sales/
    ├── stock/
    └── products/
```

### 2. Log Files
Log laporan tersimpan di:
```
storage/logs/reports.log
```

### 3. Cleanup Otomatis
Laporan lama (>1 tahun) akan dihapus otomatis setiap bulan.

## Customization

### 1. Template PDF
Edit file Blade di `resources/views/reports/` untuk mengubah tampilan PDF.

### 2. Format Excel
Modify export classes untuk mengubah struktur dan styling Excel.

### 3. Filter Tambahan
Tambahkan filter baru di `ReportService` dan `ReportController`.

### 4. Periode Laporan
Tambahkan periode baru (quarterly, yearly) dengan membuat command dan method baru.

## Troubleshooting

### 1. Error PDF Generation
- Pastikan extension `php-gd` terinstall
- Check permission storage folder
- Verify template Blade syntax

### 2. Error Excel Export
- Pastikan package `maatwebsite/excel` terinstall
- Check memory limit untuk file besar
- Verify data format

### 3. Scheduler Tidak Berjalan
- Pastikan cron job ter-setup
- Check Laravel logs
- Verify command bisa dijalankan manual

### 4. Storage Issues
- Pastikan `storage:link` sudah dibuat
- Check folder permissions
- Verify disk configuration

## API Response Examples

### Success Response
```json
{
    "success": true,
    "data": {
        "sales": [...],
        "summary": {
            "total_sales": 25,
            "total_amount": 1500000,
            "total_items": 150,
            "average_transaction": 60000,
            "top_products": [...]
        }
    }
}
```

### Error Response
```json
{
    "message": "The given data was invalid.",
    "errors": {
        "period": ["The period field is required."]
    }
}
```

## Security

### 1. Authentication
Semua endpoint laporan memerlukan authentication via Sanctum.

### 2. Authorization
Implementasikan middleware role untuk membatasi akses berdasarkan user role.

### 3. Rate Limiting
Tambahkan rate limiting untuk mencegah abuse.

## Performance Tips

### 1. Database Indexing
Pastikan kolom berikut ter-index:
- `created_at` pada semua tabel
- `product_id`, `sale_id` pada tabel relasi
- `user_id` pada tabel sales

### 2. Query Optimization
- Gunakan eager loading untuk relasi
- Implementasikan pagination untuk data besar
- Cache hasil query yang sering diakses

### 3. File Management
- Compress file PDF/Excel lama
- Implementasikan cleanup otomatis
- Monitor disk usage

## Monitoring

### 1. Log Monitoring
Monitor file `storage/logs/reports.log` untuk:
- Success/failure generation
- Performance metrics
- Error details

### 2. Storage Monitoring
Monitor folder `storage/app/reports/` untuk:
- File count
- Disk usage
- File age

### 3. Performance Monitoring
Monitor:
- Generation time
- File size
- Memory usage

## Support

Untuk bantuan teknis atau pertanyaan, silakan buat issue di repository atau hubungi tim development.

