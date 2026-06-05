<?php

namespace App\Http\Controllers;

use App\Http\Requests\DebtSettlementRequest;
use App\Http\Requests\SharedExpenseRequest;
use App\Models\DebtSettlement;
use App\Models\Housemate;
use App\Models\SharedExpense;
use App\Services\ActivityLogService;
use App\Services\DebtLedgerService;
use App\Services\MoneySplitService;
use Illuminate\Support\Facades\DB;

class DebtController extends Controller
{
    public function index(DebtLedgerService $debtLedgerService)
    {
        return view('debts.index', [
            'sharedExpenses' => SharedExpense::query()->with(['payer', 'participants.housemate', 'settlements'])->latest('expense_date')->get(),
            'housemates' => Housemate::active()->orderBy('name')->get(),
            'outstandingBalances' => $debtLedgerService->outstandingBalances(),
        ]);
    }

    public function store(SharedExpenseRequest $request, MoneySplitService $moneySplitService, ActivityLogService $activityLogService)
    {
        DB::transaction(function () use ($request, $moneySplitService, $activityLogService) {
            $data = $request->validated();
            $housemateIds = array_values($data['housemate_ids']);
            unset($data['housemate_ids']);

            $expense = SharedExpense::query()->create($data);
            $shares = $moneySplitService->splitEvenly((int) $expense->amount, count($housemateIds));

            foreach ($housemateIds as $index => $housemateId) {
                $expense->participants()->create([
                    'housemate_id' => $housemateId,
                    'share_amount' => $shares[$index],
                ]);
            }

            $activityLogService->log('debt.expense', "Created shared expense {$expense->title}.", $expense);
        });

        return back()->with('status', 'Shared expense created.');
    }

    public function settle(DebtSettlementRequest $request, ActivityLogService $activityLogService)
    {
        $settlement = DebtSettlement::query()->create($request->validated());
        $activityLogService->log('debt.settlement', 'Recorded a debt settlement.', $settlement);

        return back()->with('status', 'Settlement recorded.');
    }
}
