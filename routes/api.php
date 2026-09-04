<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\WalletController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(fn () => [
    Route::post('register', [AuthController::class, 'register']),
    Route::post('login', [AuthController::class, 'login']),
    Route::middleware('auth:sanctum')->get('logout', [AuthController::class, 'logout']),
]);

Route::middleware('auth:sanctum')->group(fn () => [
    Route::get('wallet', [WalletController::class, 'show']),
    Route::post('topup', [WalletController::class, 'deposit']),
    Route::post('transfer', [WalletController::class, 'transfer']),
    Route::get('transactions', [TransactionController::class, 'show']),
]);
