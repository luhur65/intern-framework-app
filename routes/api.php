<?php

use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::apiResource('/penjualan', ApiController::class);
Route::get('/v2/penjualan', [ApiController::class, 'v2'])->name('v2');