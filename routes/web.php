<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SessionsController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/login');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::middleware(['role:admin'])->group(function () {
        Route::resource('products', ProductController::class);
        Route::get('/products/{product}/update-stock', [ProductController::class, 'showUpdateStock'])->name('products.stock.show');
        Route::patch('/products/{product}/stock', [ProductController::class, 'updateStock'])->name('products.stock.update');
        Route::get('/products/{product}/stock-history', [ProductController::class, 'stockHistory'])->name('products.stock.history');
        Route::get('/stock-history', [ProductController::class, 'allStockHistory'])->name('stock.history.all');
        Route::get('/products/{product}/edit-sku', [ProductController::class, 'editSku'])->name('products.sku.edit');
        Route::patch('/products/{product}/sku', [ProductController::class, 'updateSku'])->name('products.sku.update');
        Route::resource('categories', CategoryController::class);
    });

    Route::middleware(['role:admin'])->group(function () {
        Route::resource('users', UserController::class);
    });

    Route::middleware(['role:admin'])->group(function () {
        Route::get('/settings', [SettingsController::class, 'edit'])->name('settings.edit');
        Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
    });

    Route::middleware(['role:admin'])->group(function () {
        Route::get('/sessions', [SessionsController::class, 'index'])->name('sessions.index');
        Route::delete('/sessions/{session}/force-logout', [SessionsController::class, 'forceLogout'])->name('sessions.force-logout');
    });

    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
    Route::get('/transactions/create', [TransactionController::class, 'create'])->name('transactions.create');
    Route::post('/transactions', [TransactionController::class, 'store'])->name('transactions.store');
    Route::get('/transactions/products', [TransactionController::class, 'getProducts'])->name('transactions.products');
    Route::get('/transactions/{transaction}', [TransactionController::class, 'show'])->name('transactions.show');

    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::post('/reports/generate', [ReportController::class, 'generateReport'])->name('reports.generate');
    Route::get('/receipt/{transaction}/download', [ReportController::class, 'downloadReceipt'])->name('receipt.download');
});

require __DIR__.'/auth.php';
