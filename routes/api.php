<?php

use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::get('/orders', [OrderController::class, 'index']);
Route::post('/orders', [OrderController::class, 'store']);
Route::get('/orders/{id}', [OrderController::class, 'show']);
Route::get('/orders/user/{userId}', [OrderController::class, 'getOrdersByUser']);
Route::get('/orders/product/{productId}', [OrderController::class, 'getOrdersByProduct']);
Route::put('/orders/{id}/complete', [OrderController::class, 'completeOrder']);
Route::get('/orders/status/completed', [OrderController::class, 'getCompletedOrders']);