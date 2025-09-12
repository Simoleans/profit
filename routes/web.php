<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\HeaderController;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth'])->name('dashboard');

Route::resource('users', UserController::class)->middleware(['auth'])->names('users');
Route::get('search-seller', [UserController::class, 'searchSeller'])->middleware(['auth'])->name('search.seller');

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
Route::get('search-clients', [HeaderController::class, 'searchClients'])->middleware(['auth'])->name('search.clients');
Route::get('search-articles', [HeaderController::class, 'searchArticles'])->middleware(['auth'])->name('search.articles');
Route::get('check-client', [HeaderController::class, 'checkClient'])->middleware(['auth'])->name('check.client');
Route::get('debug-user-data', [HeaderController::class, 'debugUserData'])->middleware(['auth'])->name('debug.user.data');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
