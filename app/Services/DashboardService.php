<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Expense;
use App\Models\ShoppingItem;
use Carbon\Carbon;

class DashboardService
{
    public function __construct(
        private readonly DebtLedgerService $debtLedgerService,
    ) {}

    public function summary(): array
    {
        $monthStart = Carbon::now()->startOfMonth()->toDateString();

        $currentExpenses = Expense::query()
            ->whereDate('expense_date', '>=', $monthStart)
            ->with(['payer', 'splits.member', 'category'])
            ->get();

        $expensesTotal = Expense::query()
            ->whereDate('expense_date', '>=', $monthStart)
            ->sum('amount');

        return [
            'currentExpenses' => $currentExpenses,
            'expensesTotal' => (int) $expensesTotal,
            'outstandingDebts' => $this->debtLedgerService->outstandingBalances(),
            'shoppingPending' => ShoppingItem::query()->where('is_purchased', false)->count(),
            'recentActivities' => ActivityLog::query()->latest()->limit(8)->get(),
        ];
    }
}
