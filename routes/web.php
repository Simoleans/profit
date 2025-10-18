<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\HeaderController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Api\DashboardStatsController;

Route::get('/', function () {
    //return Inertia::render('Welcome');
    return redirect()->route('login');
})->name('home');

Route::get('dashboard', [DashboardController::class, 'index'])->middleware(['auth'])->name('dashboard');

// API routes para estadísticas del dashboard
Route::prefix('api/dashboard')->middleware(['auth'])->group(function () {
    Route::get('stats/clients', [DashboardStatsController::class, 'clients'])->name('api.dashboard.stats.clients');
    Route::get('stats/retenciones', [DashboardStatsController::class, 'retenciones'])->name('api.dashboard.stats.retenciones');
    Route::get('stats/cuentas-por-cobrar', [DashboardStatsController::class, 'cuentasPorCobrar'])->name('api.dashboard.stats.cuentas-por-cobrar');
    Route::get('promotion-articles', [DashboardStatsController::class, 'promotionArticles'])->name('api.dashboard.promotion-articles');
    Route::get('order-stats', [DashboardStatsController::class, 'orderStats'])->name('api.dashboard.order-stats');
    Route::get('clientes-sin-pedidos', [DashboardStatsController::class, 'clientesSinPedidos'])->name('api.dashboard.clientes-sin-pedidos');
    Route::get('clientes-inactivos', [DashboardStatsController::class, 'clientesInactivos'])->name('api.dashboard.clientes-inactivos');
});

Route::resource('users', UserController::class)->middleware(['auth'])->names('users');
Route::get('search-seller', [UserController::class, 'searchSeller'])->middleware(['auth'])->name('search.seller');

// Ruta para descargar documentos (debe ir antes del resource)
Route::get('clients/media/{mediaId}/download', [ClientController::class, 'downloadDocument'])->middleware(['auth'])->name('clients.media.download');
Route::get('clients/balance-detail/{co_cli}', [ClientController::class, 'balanceDetail'])->middleware(['auth'])->name('clients.balance-detail');
Route::get('clientes-sin-pedidos', [\App\Http\Controllers\ClientesSinPedidosController::class, 'index'])->middleware(['auth'])->name('clientes-sin-pedidos');
Route::resource('clients', ClientController::class)->middleware(['auth'])->names('clients');

Route::resource('articles', ArticleController::class)->middleware(['auth'])->names('articles');

// Rutas para pedidos
Route::get('orders', [HeaderController::class, 'index'])->middleware(['auth'])->name('orders.index');
Route::get('orders/create', [HeaderController::class, 'create'])->middleware(['auth'])->name('orders.create');
Route::post('orders', [HeaderController::class, 'store'])->middleware(['auth'])->name('orders.store');
Route::get('orders/{fact_num}', [HeaderController::class, 'show'])->middleware(['auth'])->name('orders.show');
Route::get('orders/{fact_num}/edit', [HeaderController::class, 'edit'])->middleware(['auth'])->name('orders.edit');
Route::put('orders/{fact_num}', [HeaderController::class, 'update'])->middleware(['auth'])->name('orders.update');
Route::delete('orders/{fact_num}', [HeaderController::class, 'destroy'])->middleware(['auth'])->name('orders.destroy');
Route::post('orders/{fact_num}/approve', [HeaderController::class, 'approve'])->middleware(['auth'])->name('orders.approve');
Route::post('orders/{fact_num}/resend-email', [HeaderController::class, 'resendEmail'])->middleware(['auth'])->name('orders.resend-email');
Route::get('search-clients', [HeaderController::class, 'searchClients'])->middleware(['auth'])->name('search.clients');
Route::get('search-articles', [HeaderController::class, 'searchArticles'])->middleware(['auth'])->name('search.articles');
Route::get('check-client', [HeaderController::class, 'checkClient'])->middleware(['auth'])->name('check.client');
Route::get('debug-user-data', [HeaderController::class, 'debugUserData'])->middleware(['auth'])->name('debug.user.data');



require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
