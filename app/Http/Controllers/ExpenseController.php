<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExpenseRequest;
use App\Models\Category;
use App\Models\Expense;
use App\Models\ExpenseSplit;
use App\Models\Member;
use App\Services\ActivityLogService;
use App\Services\DebtLedgerService;
use App\Services\MoneySplitService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExpenseController extends Controller
{
    public function index()
    {
        return view('expenses.index', [
            'expenses' => Expense::query()
                ->with(['payer', 'splits.member', 'category'])
                ->latest('expense_date')
                ->get(),
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

            $activityLogService->log('expense.created', "Created expense {$expense->description}.", $expense);
        });

        return back()->with('status', 'Expense created.');
    }

    public function update(ExpenseRequest $request, Expense $expense, MoneySplitService $moneySplitService, ActivityLogService $activityLogService)
    {
        if ($expense->is_fully_settled) {
            return back()->with('error', __('This expense is fully settled and cannot be edited.'));
        }

        DB::transaction(function () use ($request, $expense, $moneySplitService, $activityLogService) {
            $data = $request->validated();
            $memberIds = array_values($data['member_ids']);
            unset($data['member_ids']);

            $expense->update($data);
            $expense->splits()->delete();

            $shares = $moneySplitService->splitEvenly((int) $expense->amount, count($memberIds));

            foreach ($memberIds as $index => $memberId) {
                $expense->splits()->create([
                    'member_id' => $memberId,
                    'amount_owed' => $shares[$index],
                ]);
            }

            $activityLogService->log('expense.updated', "Updated expense {$expense->description}.", $expense);
        });

        return back()->with('status', 'Expense updated.');
    }

    public function destroy(Expense $expense, ActivityLogService $activityLogService)
    {
        if ($expense->is_fully_settled) {
            return back()->with('error', __('This expense is fully settled and cannot be edited.'));
        }

        $expense->delete();
        $activityLogService->log('expense.deleted', "Deleted expense {$expense->description}.", $expense);

        return back()->with('status', 'Expense deleted.');
    }

    public function toggleSplit(Request $request, ExpenseSplit $split, ActivityLogService $activityLogService)
    {
        $data = $request->validate([
            'is_settled' => ['required', 'boolean'],
        ]);

        $split->update($data);

        $activityLogService->log(
            'expense_split.settled',
            "Marked split for {$split->member?->name} as ".($split->is_settled ? 'settled' : 'unsettled').'.',
            $split,
            ['is_settled' => $split->is_settled],
        );

        return back();
    }
}
