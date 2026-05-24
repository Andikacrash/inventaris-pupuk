<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\ProductController as BladeProductController;
use App\Http\Controllers\AuthController;

// Testing route untuk products
Route::get('/test/products', [ProductController::class, 'index']);

// Auth routes (tampilan login pakai layouts.guest — tanpa sidebar)
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Buat akun hanya untuk admin (bukan pendaftaran publik)
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Halaman utama setelah login langsung ke kasir
Route::get('/', function () {
    return redirect('/sales');
})->middleware('auth')->name('dashboard');

// Dashboard ringkasan
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'role:admin,kasir,manager'])->name('dashboard.index');

// Produk CRUD - Hanya Admin yang bisa akses
Route::get('/products', [BladeProductController::class, 'index'])
    ->middleware(['auth', 'role:admin'])
    ->name('products.index');
Route::get('/products/create', [BladeProductController::class, 'create'])
    ->middleware(['auth', 'role:admin'])
    ->name('products.create');
Route::post('/products', [BladeProductController::class, 'store'])
    ->middleware(['auth', 'role:admin'])
    ->name('products.store');
Route::get('/products/{product}/edit', [BladeProductController::class, 'edit'])
    ->middleware(['auth', 'role:admin'])
    ->name('products.edit');
Route::put('/products/{product}', [BladeProductController::class, 'update'])
    ->middleware(['auth', 'role:admin'])
    ->name('products.update');
Route::delete('/products/{product}', [BladeProductController::class, 'destroy'])
    ->middleware(['auth', 'role:admin'])
    ->name('products.destroy');

Route::get('/suppliers', [\App\Http\Controllers\SupplierController::class, 'index'])
    ->middleware(['auth', 'role:admin'])
    ->name('suppliers.index');
Route::post('/suppliers', [\App\Http\Controllers\SupplierController::class, 'store'])
    ->middleware(['auth', 'role:admin'])
    ->name('suppliers.store');

// Penjualan - Admin dan Kasir bisa akses
Route::get('/sales', function () {
    return view('sales');
})->middleware(['auth', 'role:admin,kasir']);

// Hutang - Admin dan Kasir bisa akses
Route::get('/debts', [\App\Http\Controllers\DebtController::class, 'index'])
    ->middleware(['auth', 'role:admin,kasir'])
    ->name('debts.index');
Route::get('/debts/{debt}', [\App\Http\Controllers\DebtController::class, 'show'])
    ->middleware(['auth', 'role:admin,kasir'])
    ->name('debts.show');
Route::post('/debts/{debt}/payment', [\App\Http\Controllers\DebtController::class, 'recordPayment'])
    ->middleware(['auth', 'role:admin,kasir'])
    ->name('debts.record-payment');
Route::post('/debts/bulk-payment', [\App\Http\Controllers\DebtController::class, 'recordBulkPayment'])
    ->middleware(['auth', 'role:admin,kasir'])
    ->name('debts.bulk-payment');
Route::put('/debts/payments/{payment}', [\App\Http\Controllers\DebtController::class, 'updatePayment'])
    ->middleware(['auth', 'role:admin,kasir'])
    ->name('debts.update-payment');
Route::delete('/debts/payments/{payment}', [\App\Http\Controllers\DebtController::class, 'deletePayment'])
    ->middleware(['auth', 'role:admin,kasir'])
    ->name('debts.delete-payment');

// Installment routes
Route::post('/debts/{debt}/installments', [\App\Http\Controllers\InstallmentController::class, 'createPlan'])
    ->middleware(['auth', 'role:admin,kasir'])
    ->name('installments.create-plan');
Route::post('/installments/{installmentPayment}/pay', [\App\Http\Controllers\InstallmentController::class, 'payInstallment'])
    ->middleware(['auth', 'role:admin,kasir'])
    ->name('installments.pay');
Route::post('/installments/{plan}/cancel', [\App\Http\Controllers\InstallmentController::class, 'cancelPlan'])
    ->middleware(['auth', 'role:admin,kasir'])
    ->name('installments.cancel');

// Laporan - Admin dan Manager bisa akses
Route::get('/reports', function () {
    return view('reports');
})->middleware(['auth', 'role:admin,kasir']);

// Laporan Stok - Admin dan Kasir
Route::get('/stock-reports', function () {
    return view('stock-reports');
})->middleware(['auth', 'role:admin,kasir']);

Route::middleware(['auth', 'role:admin,kasir'])->group(function () {
    Route::get('/stock-reports/download/excel', [\App\Http\Controllers\Api\ReportController::class, 'downloadStockExcel'])
        ->name('stock-reports.download.excel');
});
