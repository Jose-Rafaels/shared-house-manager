<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExpenseRequest;
use App\Models\Category;
use App\Models\Expense;
use App\Models\Member;
use App\Services\ActivityLogService;
use App\Services\DebtLedgerService;
use App\Services\MoneySplitService;
use Illuminate\Support\Facades\DB;

class ExpenseController extends Controller
{
    public function index()
    {
        return view('expenses.index', [
            'expenses' => Expense::query()
                ->with(['payer', 'splits.member', 'category'])
                ->latest('expense_date')
                ->paginate(15)
                ->withQueryString(),
            'members' => Member::query()->active()->orderBy('name')->get(),
            'categories' => Category::query()->orderBy('name')->get(),
        ]);
    }

    public function store(ExpenseRequest $request, MoneySplitService $moneySplitService, DebtLedgerService $debtLedgerService, ActivityLogService $activityLogService)
    {
        DB::transaction(function () use ($request, $moneySplitService, $debtLedgerService, $activityLogService) {
            $data = $request->validated();
            $memberIds = array_values($data['member_ids']);
            unset($data['member_ids']);

            $expense = Expense::query()->create($data);
            $shares = $moneySplitService->splitEvenly((int) $expense->amount, count($memberIds));

            foreach ($memberIds as $index => $memberId) {
                $debtLedgerService->netShare($expense, (int) $memberId, (int) $shares[$index]);
            }

            $activityLogService->log('expense.created', "Pengeluaran {$expense->description}.", $expense);
        });

        return back()->with('status', 'Expense created.');
    }

    public function destroy(Expense $expense, ActivityLogService $activityLogService)
    {
        $expense->delete();
        $activityLogService->log('expense.deleted', "Deleted expense {$expense->description}.", $expense);

        return back()->with('status', 'Expense deleted.');
    }
}
