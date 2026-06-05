<?php

namespace Tests\Unit;

use App\Models\DebtSettlement;
use App\Models\Housemate;
use App\Models\SharedExpense;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DebtLedgerServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_outstanding_balances_exclude_payer_and_subtract_settlements(): void
    {
        $payer = Housemate::query()->create(['name' => 'Payer']);
        $debtorA = Housemate::query()->create(['name' => 'Debtor A']);
        $debtorB = Housemate::query()->create(['name' => 'Debtor B']);

        $expense = SharedExpense::query()->create([
            'title' => 'Groceries',
            'payer_housemate_id' => $payer->id,
            'amount' => 30000,
            'expense_date' => now()->toDateString(),
        ]);

        $expense->participants()->createMany([
            ['housemate_id' => $payer->id, 'share_amount' => 10000],
            ['housemate_id' => $debtorA->id, 'share_amount' => 10000],
            ['housemate_id' => $debtorB->id, 'share_amount' => 10000],
        ]);

        DebtSettlement::query()->create([
            'shared_expense_id' => $expense->id,
            'debtor_housemate_id' => $debtorA->id,
            'creditor_housemate_id' => $payer->id,
            'amount' => 4000,
            'settled_on' => now()->toDateString(),
        ]);

        $balances = app(\App\Services\DebtLedgerService::class)->outstandingBalances();

        $this->assertCount(2, $balances);
        $this->assertSame(6000, $balances->firstWhere('debtor_housemate_id', $debtorA->id)->amount);
        $this->assertSame(10000, $balances->firstWhere('debtor_housemate_id', $debtorB->id)->amount);
    }
}
