<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
Route::post('registration', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::prefix('auth')->middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::get('profile', [AuthController::class, 'profile']);
});