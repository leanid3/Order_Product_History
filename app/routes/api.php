<?php 

use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\WarehouseController;

//Маршруты для складов и продуктов
Route::get('warehouses', [WarehouseController::class, 'index']);
Route::get('products', [ProductController::class, 'index']);

//Маршруты для заказов
Route::prefix('orders')->group(function () {
    Route::get('/', [OrderController::class, 'index']);
    Route::post('/', [OrderController::class, 'store']);
    Route::put('/{order}', [OrderController::class, 'update']);
    Route::post('/{order}/complete', [OrderController::class, 'complete']);
    Route::post('/{order}/cancel', [OrderController::class, 'cancel']);
    Route::post('/{order}/resume', [OrderController::class, 'resume']);
});

//Маршруты для авторизации
require_once __DIR__ . '/API/auth.php';