<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\RateController;
use App\Http\Controllers\BukuController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\PinjamController;


Route::resource('ratings', RateController::class)->only(['store', 'update', 'destroy', 'show']);
Route::get('ratings/page/{page}', [RateController::class, 'page'])->name('ratings.page');
Route::resource('books', BukuController::class)->only(['show', 'store', 'update', 'destroy']);
Route::get('books', [BukuController::class, 'index'])->name('books.index');
Route::get('users/{username}', function ($username) {
    $user = DB::table('anggota')->where('username', $username)->first();
    if (!$user) {
        return response()->json([
            'result' => 'ERROR',
            'message' => 'User not found',
        ], 404);
    }
    return response()->json([
        'result' => 'OK',
        'user' => $user,
    ]);
});

Route::resource('pinjam', PinjamController::class)->only(['store', 'show', 'destroy']);
Route::put('pinjam/return', [PinjamController::class, 'update']);

Route::get('/ping', function (Request  $request) {
    $connection = DB::connection('mongodb');
    try {
        $connection->getMongoClient()->listDatabases();
        return response()->json([
            'result' => 'OK',
            'message' => 'MongoDB connection is active',
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'result' => 'ERROR',
            'message' => 'MongoDB connection failed: ' . $e->getMessage(),
        ], 500);
    }
});