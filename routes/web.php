<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\RateController;
use App\Models\Anggota;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/book', function () {
    return Inertia::render('Books');
})->middleware(['auth', 'verified'])->name('book');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
