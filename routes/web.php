<?php

use \App\Http\Controllers\PenjualanDetailController;
use App\Http\Controllers\PenjualanController;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });

// Route Penjualan
Route::match(['GET', 'POST'],  '/', [PenjualanController::class, 'index'])->name('penjualan.index');
// Route::get('/', [PenjualanController::class, 'index'])->name('penjualan.index');
Route::get('penjualan/master', [PenjualanController::class, 'master'])->name('penjualan.master');
Route::resource('penjualan', PenjualanController::class)->except(['index']);

// Route Penjualan Detail
// Route::get('detail/', [PenjualanDetailController::class, 'index'])
//     ->name('penjualan.detail.index');
Route::get('penjualan/{penjualanId?}/detail', [PenjualanDetailController::class, 'getDetail'])
    ->name('penjualan.detail.getDetail'); // arti (penjualanId?) adalah opsional, bisa diisi atau tidak

// Penjualan Export
Route::get('penjualan/export/{mode}', [PenjualanController::class, 'export'])->name('penjualan.export');