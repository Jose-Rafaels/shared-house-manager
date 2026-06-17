<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExpenseRequest;
use App\Models\Category;
use App\Models\Expense;
use App\Models\Member;
use App\Services\ActivityLogService;
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
                ->get(),
            'members' => Member::query()->orderBy('name')->get(),
            'categories' => Category::query()->orderBy('name')->get(),
        ]);
    }

    public function store(ExpenseRequest $request, MoneySplitService $moneySplitService, ActivityLogService $activityLogService)
    {
        DB::transaction(function () use ($request, $moneySplitService, $activityLogService) {
            $data = $request->validated();
            $memberIds = array_values($data['member_ids']);
            unset($data['member_ids']);

            $expense = Expense::query()->create($data);
            $shares = $moneySplitService->splitEvenly((int) $expense->amount, count($memberIds));

            foreach ($memberIds as $index => $memberId) {
                $expense->splits()->create([
                    'member_id' => $memberId,
                    'amount_owed' => $shares[$index],
                ]);
            }

            $activityLogService->log('expense.created', "Created expense {$expense->description}.", $expense);
        });

        return back()->with('status', 'Expense created.');
    }

    public function update(ExpenseRequest $request, Expense $expense, MoneySplitService $moneySplitService, ActivityLogService $activityLogService)
    {
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
        $expense->delete();
        $activityLogService->log('expense.deleted', "Deleted expense {$expense->description}.", $expense);

        return back()->with('status', 'Expense deleted.');
    }
}
