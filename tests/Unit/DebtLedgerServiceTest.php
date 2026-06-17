<?php

namespace Tests\Unit;

use App\Models\Expense;
use App\Models\Member;
use App\Models\Settlement;
use App\Services\DebtLedgerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DebtLedgerServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_outstanding_balances_exclude_payer_and_subtract_settlements(): void
    {
        $payer = Member::query()->create(['name' => 'Alice']);
        $debtorA = Member::query()->create(['name' => 'Bob']);
        $debtorB = Member::query()->create(['name' => 'Charlie']);

        $expense = Expense::query()->create([
            'payer_id' => $payer->id,
            'category_id' => null,
            'amount' => 30000,
            'description' => 'Test expense',
            'expense_date' => '2026-06-01',
        ]);

        // Split 30000 across 2 non-payer members: 15000 each
        $expense->splits()->createMany([
            ['member_id' => $debtorA->id, 'amount_owed' => 15000],
            ['member_id' => $debtorB->id, 'amount_owed' => 15000],
        ]);

        // Settle 4000 from debtorA to payer
        Settlement::query()->create([
            'from_member_id' => $debtorA->id,
            'to_member_id' => $payer->id,
            'amount' => 4000,
            'settlement_date' => '2026-06-05',
            'note' => null,
        ]);

        $service = new DebtLedgerService;
        $balances = $service->outstandingBalances();

        // debtorA owes 15000 - 4000 = 11000
        // debtorB owes 15000
        $this->assertCount(2, $balances);

        $debtorABalance = $balances->first(fn ($b) => $b->debtor_member_id === $debtorA->id);
        $this->assertEquals(11000, $debtorABalance->amount);

        $debtorBBalance = $balances->first(fn ($b) => $b->debtor_member_id === $debtorB->id);
        $this->assertEquals(15000, $debtorBBalance->amount);
    }
}
