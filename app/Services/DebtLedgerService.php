<?php

namespace App\Services;

use App\Models\DebtSettlement;
use App\Models\SharedExpenseParticipant;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DebtLedgerService
{
    public function outstandingBalances(): Collection
    {
        $shares = SharedExpenseParticipant::query()
            ->select([
                'shared_expenses.payer_housemate_id as creditor_housemate_id',
                'shared_expense_participants.housemate_id as debtor_housemate_id',
                DB::raw('SUM(shared_expense_participants.share_amount) as total_share'),
            ])
            ->join('shared_expenses', 'shared_expenses.id', '=', 'shared_expense_participants.shared_expense_id')
            ->whereColumn('shared_expense_participants.housemate_id', '!=', 'shared_expenses.payer_housemate_id')
            ->groupBy('shared_expenses.payer_housemate_id', 'shared_expense_participants.housemate_id')
            ->get()
            ->keyBy(fn ($row) => $row->debtor_housemate_id.'-'.$row->creditor_housemate_id);

        $settlements = DebtSettlement::query()
            ->select([
                'creditor_housemate_id',
                'debtor_housemate_id',
                DB::raw('SUM(amount) as total_settled'),
            ])
            ->groupBy('creditor_housemate_id', 'debtor_housemate_id')
            ->get()
            ->keyBy(fn ($row) => $row->debtor_housemate_id.'-'.$row->creditor_housemate_id);

        return $shares->map(function ($share, string $key) use ($settlements) {
            $settled = (int) optional($settlements->get($key))->total_settled;

            return (object) [
                'debtor_housemate_id' => $share->debtor_housemate_id,
                'creditor_housemate_id' => $share->creditor_housemate_id,
                'amount' => max(0, (int) $share->total_share - $settled),
            ];
        })->filter(fn ($entry) => $entry->amount > 0)->values();
    }
}
