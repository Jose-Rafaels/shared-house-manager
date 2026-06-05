<?php

use App\Http\Controllers\BillController;
use App\Http\Controllers\CashFundController;
use App\Http\Controllers\ChoreController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DebtController;
use App\Http\Controllers\HousemateController;
use App\Http\Controllers\ShoppingController;
use Illuminate\Support\Facades\Route;

Route::get('/', DashboardController::class)->name('dashboard');

Route::get('/housemates', [HousemateController::class, 'index'])->name('housemates.index');
Route::post('/housemates', [HousemateController::class, 'store'])->name('housemates.store');
Route::put('/housemates/{housemate}', [HousemateController::class, 'update'])->name('housemates.update');
Route::patch('/housemates/{housemate}/archive', [HousemateController::class, 'archive'])->name('housemates.archive');

Route::get('/bills', [BillController::class, 'index'])->name('bills.index');
Route::post('/bills', [BillController::class, 'store'])->name('bills.store');
Route::post('/bills/{bill}/payments', [BillController::class, 'markPaid'])->name('bills.payments.store');

Route::get('/cash-fund', [CashFundController::class, 'index'])->name('cash-fund.index');
Route::post('/cash-fund', [CashFundController::class, 'store'])->name('cash-fund.store');

Route::get('/debts', [DebtController::class, 'index'])->name('debts.index');
Route::post('/debts/expenses', [DebtController::class, 'store'])->name('debts.expenses.store');
Route::post('/debts/settlements', [DebtController::class, 'settle'])->name('debts.settlements.store');

Route::get('/chores', [ChoreController::class, 'index'])->name('chores.index');
Route::post('/chores', [ChoreController::class, 'store'])->name('chores.store');
Route::patch('/chores/assignments/{assignment}/complete', [ChoreController::class, 'complete'])->name('chores.assignments.complete');

Route::get('/shopping', [ShoppingController::class, 'index'])->name('shopping.index');
Route::post('/shopping', [ShoppingController::class, 'store'])->name('shopping.store');
Route::post('/shopping/{shoppingItem}/purchase', [ShoppingController::class, 'markPurchased'])->name('shopping.purchase.store');
