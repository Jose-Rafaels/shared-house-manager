<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Bill;
use App\Models\BillParticipant;
use App\Models\BillPayment;
use App\Models\ChoreAssignment;
use App\Models\ShoppingItem;
use Carbon\Carbon;

class DashboardService
{
    public function __construct(
        private readonly CashFundService $cashFundService,
        private readonly DebtLedgerService $debtLedgerService,
    ) {
    }

    public function summary(): array
    {
        $monthStart = Carbon::now()->startOfMonth()->toDateString();

        $currentBills = Bill::query()
            ->whereDate('billing_month', $monthStart)
            ->withCount('participants')
            ->get();

        $unpaidTotal = BillParticipant::query()
            ->leftJoin('bill_payments', function ($join) {
                $join->on('bill_participants.bill_id', '=', 'bill_payments.bill_id')
                    ->on('bill_participants.housemate_id', '=', 'bill_payments.housemate_id');
            })
            ->selectRaw('COALESCE(SUM(bill_participants.share_amount), 0) - COALESCE(SUM(bill_payments.amount), 0) as balance')
            ->value('balance') ?? 0;

        return [
            'currentBills' => $currentBills,
            'unpaidTotal' => (int) $unpaidTotal,
            'cashBalance' => $this->cashFundService->balance(),
            'outstandingDebts' => $this->debtLedgerService->outstandingBalances(),
            'currentChores' => ChoreAssignment::query()
                ->with(['chore', 'housemate'])
                ->whereDate('assigned_for_date', '<=', Carbon::now()->endOfWeek()->toDateString())
                ->latest('assigned_for_date')
                ->limit(5)
                ->get(),
            'shoppingPending' => ShoppingItem::query()->whereNull('purchased_at')->count(),
            'recentActivities' => ActivityLog::query()->latest()->limit(8)->get(),
            'recentBillPayments' => BillPayment::query()->latest('payment_date')->limit(5)->get(),
        ];
    }
}
