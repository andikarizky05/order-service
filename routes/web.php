<?php

use App\Http\Controllers\OrderViewController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group.
|
*/

Route::get('/', [OrderViewController::class, 'dashboard'])->name('dashboard');
Route::get('/orders', [OrderViewController::class, 'index'])->name('orders.index');
Route::get('/orders/create', [OrderViewController::class, 'create'])->name('orders.create');
Route::get('/orders/completed', [OrderViewController::class, 'completedOrders'])->name('orders.completed');
Route::get('/orders/{id}', [OrderViewController::class, 'show'])->name('orders.show');
