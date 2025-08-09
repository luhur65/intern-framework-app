<?php

use \App\Http\Controllers\PenjualanDetailController;
use App\Http\Controllers\PenjualanController;
use App\Http\Middleware\BlockMethodRoute;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });

// Route Penjualan
Route::match(['GET', 'POST'],  '/', [PenjualanController::class, 'index'])->name('penjualan.index');
Route::get('/penjualan/export', function () {
    abort(404);
})->name('penjualan.show');
// Route::get('/', [PenjualanController::class, 'index'])->name('penjualan.index');
Route::get('penjualan/master', [PenjualanController::class, 'master'])->name('penjualan.master');
Route::resource('penjualan', PenjualanController::class)->except(['index' ,'show']);

// Route Penjualan Detail
// Route::get('detail/', [PenjualanDetailController::class, 'index'])
//     ->name('penjualan.detail.index');
Route::get('penjualan/{penjualanId?}/detail', [PenjualanDetailController::class, 'getDetail'])
    ->name('penjualan.detail.getDetail'); // arti (penjualanId?) adalah opsional, bisa diisi atau tidak

// Penjualan Export
// Route::post('/penjualan/export/validasi', [PenjualanController::class, 'validateExport'])->name('penjualan.export.validate');
Route::post('penjualan/export/{mode}', [PenjualanController::class, 'export'])->name('penjualan.export')->middleware(BlockMethodRoute::class);
Route::get('/penjualan/report/viewpdf', [PenjualanController::class, 'showPdfReport'])->name('penjualan.report.view');