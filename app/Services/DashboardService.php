<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\ChoreAssignment;
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
            ->whereNotNull('category_id')
            ->whereDate('expense_date', '>=', $monthStart)
            ->with(['payer', 'splits.member', 'category'])
            ->get();

        $expensesTotal = Expense::query()
            ->whereNotNull('category_id')
            ->whereDate('expense_date', '>=', $monthStart)
            ->sum('amount');

        return [
            'currentExpenses' => $currentExpenses,
            'expensesTotal' => (int) $expensesTotal,
            'outstandingDebts' => $this->debtLedgerService->outstandingBalances(),
            'currentChores' => ChoreAssignment::query()
                ->with(['chore', 'member'])
                ->whereDate('assigned_for_date', '<=', Carbon::now()->endOfWeek()->toDateString())
                ->latest('assigned_for_date')
                ->limit(5)
                ->get(),
            'shoppingPending' => ShoppingItem::query()->whereNull('purchased_at')->count(),
            'recentActivities' => ActivityLog::query()->latest()->limit(8)->get(),
        ];
    }
}
