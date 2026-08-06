<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\ExpenseSplit;
use App\Models\Member;
use App\Models\Settlement;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DebtLedgerService
{
    public function outstandingBalances(): Collection
    {
        $shares = ExpenseSplit::query()
            ->select([
                'expenses.payer_id as creditor_member_id',
                'expense_splits.member_id as debtor_member_id',
                DB::raw('SUM(expense_splits.amount_owed) as total_share'),
            ])
            ->join('expenses', 'expenses.id', '=', 'expense_splits.expense_id')
            ->whereColumn('expense_splits.member_id', '!=', 'expenses.payer_id')
            ->groupBy('expenses.payer_id', 'expense_splits.member_id')
            ->get()
            ->keyBy(fn ($row) => $row->debtor_member_id.'-'.$row->creditor_member_id);

        $settlements = Settlement::query()
            ->select([
                'to_member_id as creditor_member_id',
                'from_member_id as debtor_member_id',
                DB::raw('SUM(amount) as total_settled'),
            ])
            ->groupBy('to_member_id', 'from_member_id')
            ->get()
            ->keyBy(fn ($row) => $row->debtor_member_id.'-'.$row->creditor_member_id);

        $balances = $shares->map(function ($share, string $key) use ($settlements) {
            $settled = (int) optional($settlements->get($key))->total_settled;

            return (object) [
                'debtor_member_id' => $share->debtor_member_id,
                'creditor_member_id' => $share->creditor_member_id,
                // Surface credit (negative) balances so overpayments are visible
                // instead of silently dropped.
                'amount' => (int) $share->total_share - $settled,
            ];
        })->values();

        // Eager-load member names
        $memberIds = $balances->flatMap(fn ($b) => [$b->debtor_member_id, $b->creditor_member_id])->unique();
        $members = Member::query()->whereIn('id', $memberIds)->pluck('name', 'id');

        return $balances->map(function ($entry) use ($members) {
            $entry->debtor_name = $members[$entry->debtor_member_id] ?? 'Unknown';
            $entry->creditor_name = $members[$entry->creditor_member_id] ?? 'Unknown';

            return $entry;
        });
    }

    /**
     * Outstanding balance from $debtor to $creditor (excluding $ignoreSettlement for updates).
     */
    public function balanceBetween(int $debtorId, int $creditorId, ?int $ignoreSettlementId = null): int
    {
        $share = (int) ExpenseSplit::query()
            ->join('expenses', 'expenses.id', '=', 'expense_splits.expense_id')
            ->where('expenses.payer_id', $creditorId)
            ->where('expense_splits.member_id', $debtorId)
            ->sum('expense_splits.amount_owed');

        $settledQuery = Settlement::query()
            ->where('from_member_id', $debtorId)
            ->where('to_member_id', $creditorId);
        if ($ignoreSettlementId !== null) {
            $settledQuery->where('id', '!=', $ignoreSettlementId);
        }
        $settled = (int) $settledQuery->sum('amount');

        return $share - $settled;
    }

    /**
     * Apply a new expense share for $memberId owed to the expense's payer.
     *
     * If the payer already owes this member (reverse debt), net the two:
     * record a settlement for the overlap instead of a split, so mutual
     * debts aren't double-counted. Any leftover beyond the reverse debt
     * becomes a normal split.
     */
    public function netShare(Expense $expense, int $memberId, int $share): void
    {
        $payerId = (int) $expense->payer_id;

        if ($memberId === $payerId) {
            $expense->splits()->create(['member_id' => $memberId, 'amount_owed' => $share]);

            return;
        }

        $reverse = $this->balanceBetween($payerId, $memberId);

        if ($reverse <= 0) {
            $expense->splits()->create(['member_id' => $memberId, 'amount_owed' => $share]);

            return;
        }

        $settle = min($share, $reverse);

        Settlement::query()->create([
            'from_member_id' => $payerId,
            'to_member_id' => $memberId,
            'amount' => $settle,
            'settlement_date' => $expense->expense_date,
            'note' => 'Pelunasan dari expense'.($expense->description ?? "#{$expense->id}"),
        ]);

        if ($share > $reverse) {
            $expense->splits()->create(['member_id' => $memberId, 'amount_owed' => $share - $reverse]);
        }
    }
}
