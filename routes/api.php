<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\PosController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\StockController;
use App\Models\Product;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::get('/test-nomiddleware', function () {
    return response()->json(['message' => 'API tanpa middleware!']);
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Dashboard routes - Semua role bisa akses (tetap wajib login)
Route::middleware(['web', 'auth'])->prefix('dashboard')->group(function () {
    Route::get('/summary', [DashboardController::class, 'summary']);
    Route::get('/sales-chart', [DashboardController::class, 'salesChart']);
    Route::get('/sales-comparison', [DashboardController::class, 'salesComparison']);
    Route::get('/sales-by-period', [DashboardController::class, 'salesByPeriod']);
    Route::get('/sales-analysis', [DashboardController::class, 'salesAnalysis']);
    Route::get('/month-comparison', [DashboardController::class, 'monthComparison']);
    Route::get('/debt-management', [DashboardController::class, 'debtManagementSummary']);
    Route::get('/top-products', [DashboardController::class, 'topProducts']);
    Route::get('/category-chart', [DashboardController::class, 'categoryChart']);
    Route::get('/low-stock-alerts', [DashboardController::class, 'lowStockAlerts']);
    Route::get('/recent-sales', [DashboardController::class, 'recentSales']);
});

// POS routes - Admin dan Kasir (wajib login agar user_id tercatat benar)
Route::middleware(['web', 'auth'])->prefix('pos')->group(function () {
    Route::get('/search-products', [PosController::class, 'searchProducts']);
    Route::get('/product-by-barcode', [PosController::class, 'getProductByBarcode']);
    Route::post('/sales', [PosController::class, 'createSale']);
    Route::get('/sale-history', [PosController::class, 'getSaleHistory']);
    Route::get('/sale-detail/{id}', [PosController::class, 'getSaleDetail']);
    Route::delete('/cancel-sale/{id}', [PosController::class, 'cancelSale']);
});

// Report routes — butuh sesi login (sama seperti halaman web)
Route::middleware(['web', 'auth'])->prefix('reports')->group(function () {
    // Download laporan dalam format PDF
    Route::get('/download/sales/pdf', [ReportController::class, 'downloadSalesPdf']);

    // Download laporan dalam format Excel
    Route::get('/download/sales/excel', [ReportController::class, 'downloadSalesExcel']);
    Route::get('/download/stock/excel', [ReportController::class, 'downloadStockExcel']);
    Route::get('/download/product/excel', [ReportController::class, 'downloadProductExcel']);

    // Preview data laporan (untuk testing)
    Route::get('/preview/sales', [ReportController::class, 'previewSalesReport']);
    Route::get('/preview/stock', [ReportController::class, 'previewStockReport']);

    // Legacy routes (tetap ada untuk kompatibilitas)
    Route::get('/sales-report', [ReportController::class, 'salesReport']);
    Route::get('/stock', [ReportController::class, 'stockReport']);
    Route::get('/stock-movement', [ReportController::class, 'stockMovementReport']);
    Route::get('/supplier', [ReportController::class, 'supplierReport']);
    Route::get('/category', [ReportController::class, 'categoryReport']);
    Route::get('/top-selling-products', [ReportController::class, 'topSellingProducts']);
    Route::get('/sales', [ReportController::class, 'salesList']);
    Route::get('/sales-analytics', [ReportController::class, 'salesAnalytics']);
    Route::get('/sales/{id}', [ReportController::class, 'salesDetail']);
    // Update and delete single sale (used by reports UI)
    Route::put('/sales/{id}', [ReportController::class, 'updateSale']);
    Route::delete('/sales/{id}', [ReportController::class, 'deleteSale']);
});

// Stock management routes - Admin only
Route::prefix('stock')->group(function () {
    Route::get('/history', [StockController::class, 'getStockHistory']);
    Route::post('/adjust', [StockController::class, 'adjustStock']);
    Route::get('/low-stock-alerts', [StockController::class, 'getLowStockAlerts']);
    Route::get('/prediction', [StockController::class, 'getStockPrediction']);
    Route::post('/bulk-adjust', [StockController::class, 'bulkStockAdjustment']);
});

// Product routes - Admin only (nama api.products.* agar tidak bentrok dengan web products.*)
Route::apiResource('products', ProductController::class)->names('api.products');

// Categories route untuk filter laporan
Route::get('/categories', function () {
    $categories = \App\Models\Category::query()
        ->withCount('products')
        ->orderBy('sort_order')
        ->orderBy('name')
        ->get();

    return response()->json([
        'data' => $categories,
        'total_products' => Product::query()->count(),
    ]);
});

// Testing route
Route::get('/test', function () {
    return response()->json(['message' => 'API is working!']);
});
