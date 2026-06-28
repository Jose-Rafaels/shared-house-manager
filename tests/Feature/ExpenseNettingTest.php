<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Services\DebtLedgerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpenseNettingTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_share_smaller_than_reverse_debt_nets_to_settlement(): void
    {
        $a = Member::query()->create(['name' => 'Alice']);
        $c = Member::query()->create(['name' => 'Charlie']);

        // C owes A 100 (expense paid by A, only C as participant).
        $this->post(route('expenses.store'), [
            'description' => 'Groceries',
            'payer_id' => $a->id,
            'amount' => 100,
            'expense_date' => '2026-06-01',
            'member_ids' => [$c->id],
        ])->assertRedirect();

        // A would owe C 60; mutual debt -> net instead of new split.
        $this->post(route('expenses.store'), [
            'description' => 'Dinner',
            'payer_id' => $c->id,
            'amount' => 60,
            'expense_date' => '2026-06-02',
            'member_ids' => [$a->id],
        ])->assertRedirect();

        $this->assertDatabaseCount('expense_splits', 1); // only the original C->A split
        $this->assertDatabaseHas('settlements', [
            'from_member_id' => $c->id,
            'to_member_id' => $a->id,
            'amount' => 60,
            'note' => 'Settlement from Expense Dinner',
        ]);

        $ledger = app(DebtLedgerService::class);
        $this->assertSame(40, $ledger->balanceBetween($c->id, $a->id)); // C owes A 40
        $this->assertSame(0, $ledger->balanceBetween($a->id, $c->id));
    }

    public function test_new_share_larger_than_reverse_debt_settles_remainder_as_split(): void
    {
        $a = Member::query()->create(['name' => 'Alice']);
        $c = Member::query()->create(['name' => 'Charlie']);

        // C owes A 60.
        $this->post(route('expenses.store'), [
            'description' => 'Groceries',
            'payer_id' => $a->id,
            'amount' => 60,
            'expense_date' => '2026-06-01',
            'member_ids' => [$c->id],
        ])->assertRedirect();

        // A would owe C 100; reverse 60 -> settle 60, leftover split 40.
        $this->post(route('expenses.store'), [
            'description' => 'Dinner',
            'payer_id' => $c->id,
            'amount' => 100,
            'expense_date' => '2026-06-02',
            'member_ids' => [$a->id],
        ])->assertRedirect();

        $this->assertDatabaseHas('settlements', [
            'from_member_id' => $c->id,
            'to_member_id' => $a->id,
            'amount' => 60,
            'note' => 'Settlement from Expense Dinner',
        ]);
        $this->assertDatabaseHas('expense_splits', [
            'member_id' => $a->id,
            'amount_owed' => 40,
        ]);

        $ledger = app(DebtLedgerService::class);
        $this->assertSame(0, $ledger->balanceBetween($c->id, $a->id));
        $this->assertSame(40, $ledger->balanceBetween($a->id, $c->id)); // A owes C 40
    }

    public function test_no_reverse_debt_creates_normal_split(): void
    {
        $a = Member::query()->create(['name' => 'Alice']);
        $c = Member::query()->create(['name' => 'Charlie']);

        $this->post(route('expenses.store'), [
            'description' => 'Dinner',
            'payer_id' => $c->id,
            'amount' => 50,
            'expense_date' => '2026-06-02',
            'member_ids' => [$a->id],
        ])->assertRedirect();

        $this->assertDatabaseHas('expense_splits', [
            'member_id' => $a->id,
            'amount_owed' => 50,
        ]);
        $this->assertDatabaseCount('settlements', 0);
    }
}
