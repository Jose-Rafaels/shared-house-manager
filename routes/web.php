<?php

use App\Http\Controllers\ChoreController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\SettlementController;
use App\Http\Controllers\ShoppingController;
use Illuminate\Support\Facades\Route;

Route::get('/', DashboardController::class)->name('dashboard');

Route::get('/members', [MemberController::class, 'index'])->name('members.index');
Route::post('/members', [MemberController::class, 'store'])->name('members.store');
Route::put('/members/{member}', [MemberController::class, 'update'])->name('members.update');
Route::delete('/members/{member}', [MemberController::class, 'destroy'])->name('members.destroy');

Route::get('/expenses', [ExpenseController::class, 'index'])->name('expenses.index');
Route::post('/expenses', [ExpenseController::class, 'store'])->name('expenses.store');
Route::put('/expenses/{expense}', [ExpenseController::class, 'update'])->name('expenses.update');
Route::delete('/expenses/{expense}', [ExpenseController::class, 'destroy'])->name('expenses.destroy');

Route::get('/settlements', [SettlementController::class, 'index'])->name('settlements.index');
Route::post('/settlements', [SettlementController::class, 'store'])->name('settlements.store');
Route::put('/settlements/{settlement}', [SettlementController::class, 'update'])->name('settlements.update');
Route::delete('/settlements/{settlement}', [SettlementController::class, 'destroy'])->name('settlements.destroy');

Route::get('/chores', [ChoreController::class, 'index'])->name('chores.index');
Route::post('/chores', [ChoreController::class, 'store'])->name('chores.store');
Route::put('/chores/{chore}', [ChoreController::class, 'update'])->name('chores.update');
Route::delete('/chores/{chore}', [ChoreController::class, 'destroy'])->name('chores.destroy');
Route::patch('/chores/assignments/{assignment}/complete', [ChoreController::class, 'complete'])->name('chores.assignments.complete');

Route::get('/shopping', [ShoppingController::class, 'index'])->name('shopping.index');
Route::post('/shopping', [ShoppingController::class, 'store'])->name('shopping.store');
Route::put('/shopping/{shoppingItem}', [ShoppingController::class, 'update'])->name('shopping.update');
Route::delete('/shopping/{shoppingItem}', [ShoppingController::class, 'destroy'])->name('shopping.destroy');
Route::post('/shopping/{shoppingItem}/purchase', [ShoppingController::class, 'markPurchased'])->name('shopping.purchase.store');
