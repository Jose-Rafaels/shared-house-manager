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

    public function summary(?string $month = null, ?int $categoryId = null): array
    {
        $cardQuery = Expense::query();
        $this->scopeMonth($cardQuery, $month);
        if ($categoryId) {
            $cardQuery->where('category_id', $categoryId);
        }

        $currentExpenses = (clone $cardQuery)
            ->with(['payer', 'splits.member', 'category'])
            ->get();
        $expensesTotal = (int) (clone $cardQuery)->sum('amount');

        // Breakdown always shows every category's usage for the month
        // (ignores the category dropdown — it is the full-usage view).
        $breakdownQuery = Expense::query();
        $this->scopeMonth($breakdownQuery, $month);

        $categoryBreakdown = (clone $breakdownQuery)
            ->select('category_id')
            ->selectRaw('SUM(amount) as total, COUNT(*) as count')
            ->groupBy('category_id')
            ->with('category')
            ->get();

        return [
            'currentExpenses' => $currentExpenses,
            'expensesTotal' => $expensesTotal,
            'outstandingDebts' => $this->debtLedgerService->outstandingBalances(),
            'shoppingPending' => ShoppingItem::query()->where('is_purchased', false)->count(),
            'recentActivities' => ActivityLog::query()->latest()->limit(8)->get(),
            'categoryBreakdown' => $categoryBreakdown,
        ];
    }

    /**
     * Scope a query to a YYYY-MM month. No month = all months.
     */
    private function scopeMonth($query, ?string $month): void
    {
        if (! $month) {
            return;
        }
        $date = Carbon::createFromFormat('Y-m', $month);
        $query->whereYear('expense_date', $date->year)
            ->whereMonth('expense_date', $date->month);
    }

    /**
     * Distinct months that have expenses (YYYY-MM, newest first),
     * with the current month guaranteed to be present for navigation.
     */
    public function availableMonths(): \Illuminate\Support\Collection
    {
        $months = Expense::query()
            ->pluck('expense_date')
            ->map(fn ($d) => Carbon::parse($d)->format('Y-m'))
            ->unique()
            ->sortDesc()
            ->values();

        $current = now()->format('Y-m');
        if (! $months->contains($current)) {
            $months->prepend($current);
        }

        return $months;
    }
}
