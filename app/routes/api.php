<?php 

use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\WarehouseController;

Route::get('warehouses', [WarehouseController::class, 'index']);
Route::get('products', [ProductController::class, 'index']);

Route::prefix('orders')->group(function () {
    Route::get('/', [OrderController::class, 'index']);
    Route::post('/', [OrderController::class, 'store']);
    Route::put('/{order}', [OrderController::class, 'update']);
    Route::post('/{order}/complete', [OrderController::class, 'complete']);
    Route::post('/{order}/cancel', [OrderController::class, 'cancel']);
    Route::post('/{order}/resume', [OrderController::class, 'resume']);
});