<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ClientController;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::resource('users', UserController::class)->middleware(['auth', 'verified'])->names('users');
Route::get('search-seller', [UserController::class, 'searchSeller'])->middleware(['auth', 'verified'])->name('search.seller');

Route::resource('clients', ClientController::class)->middleware(['auth', 'verified'])->names('clients');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
