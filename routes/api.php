<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DebtorController;
use Illuminate\Support\Facades\Route;

// Public
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected
Route::middleware('auth:sanctum')->group(function () {

	// Auth
	Route::get('/user', [AuthController::class, 'user']);
	Route::post('/logout', [AuthController::class, 'logout']);

	// Debtors
	Route::get('/debtors', [DebtorController::class, 'index']);
	Route::get('/debtors/{debtor}/transactions', [DebtorController::class, 'transactions']);
	Route::post('/debtors', [DebtorController::class, 'store']);
	Route::get('/debtors/{debtor}', [DebtorController::class, 'show']);
	Route::put('/debtors/{debtor}', [DebtorController::class, 'update']);
	Route::delete('/debtors/{debtor}', [DebtorController::class, 'destroy']);

	// Debtor actions 
	Route::post('/debtors/{debtor}/add', [DebtorController::class, 'addAmount']);
	Route::post('/debtors/{debtor}/pay', [DebtorController::class, 'payAmount']);
});
