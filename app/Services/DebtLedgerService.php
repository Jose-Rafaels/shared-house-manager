<?php

namespace App\Services;

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
                'amount' => max(0, (int) $share->total_share - $settled),
            ];
        })->filter(fn ($entry) => $entry->amount > 0)->values();

        // Eager-load member names
        $memberIds = $balances->flatMap(fn ($b) => [$b->debtor_member_id, $b->creditor_member_id])->unique();
        $members = Member::query()->whereIn('id', $memberIds)->pluck('name', 'id');

        return $balances->map(function ($entry) use ($members) {
            $entry->debtor_name = $members[$entry->debtor_member_id] ?? 'Unknown';
            $entry->creditor_name = $members[$entry->creditor_member_id] ?? 'Unknown';

            return $entry;
        });
    }
}
