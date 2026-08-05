<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\Member;
use App\Models\Settlement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettlementFlowTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Create a debt of $amount owed by $debtor to $creditor.
     */
    private function seedDebt(Member $debtor, Member $creditor, int $amount): void
    {
        Expense::query()->create([
            'payer_id' => $creditor->id,
            'category_id' => null,
            'amount' => $amount,
            'description' => 'Test debt',
            'expense_date' => '2026-06-01',
        ])->splits()->createMany([
            ['member_id' => $debtor->id, 'amount_owed' => $amount],
        ]);
    }

    public function test_settlement_can_be_recorded(): void
    {
        $debtor = Member::query()->create(['name' => 'Alice']);
        $creditor = Member::query()->create(['name' => 'Bob']);

        // Alice owes Bob 100000.
        $this->seedDebt($debtor, $creditor, 100000);

        $this->post(route('settlements.store'), [
            'from_member_id' => $debtor->id,
            'to_member_id' => $creditor->id,
            'amount' => 50000,
            'settlement_date' => '2026-06-15',
            'note' => 'Pembayaran utang',
        ])->assertRedirect();

        $this->assertDatabaseHas('settlements', [
            'from_member_id' => $debtor->id,
            'to_member_id' => $creditor->id,
            'amount' => 50000,
        ]);
    }

    public function test_settlement_can_be_deleted(): void
    {
        $debtor = Member::query()->create(['name' => 'Alice']);
        $creditor = Member::query()->create(['name' => 'Bob']);

        $settlement = Settlement::query()->create([
            'from_member_id' => $debtor->id,
            'to_member_id' => $creditor->id,
            'amount' => 50000,
            'settlement_date' => '2026-06-15',
        ]);

        $this->delete(route('settlements.destroy', $settlement))->assertRedirect();

        $this->assertDatabaseMissing('settlements', ['id' => $settlement->id]);
    }

    public function test_self_settlement_is_rejected(): void
    {
        $alice = Member::query()->create(['name' => 'Alice']);

        $response = $this->post(route('settlements.store'), [
            'from_member_id' => $alice->id,
            'to_member_id' => $alice->id,
            'amount' => 1000,
            'settlement_date' => '2026-06-15',
        ]);

        $response->assertSessionHasErrors('from_member_id');
        $this->assertDatabaseCount('settlements', 0);
    }

    public function test_future_settlement_date_is_rejected(): void
    {
        $debtor = Member::query()->create(['name' => 'Alice']);
        $creditor = Member::query()->create(['name' => 'Bob']);

        $this->post(route('settlements.store'), [
            'from_member_id' => $debtor->id,
            'to_member_id' => $creditor->id,
            'amount' => 1000,
            'settlement_date' => '2099-01-01',
        ])->assertSessionHasErrors('settlement_date');
    }

    public function test_overpayment_is_rejected(): void
    {
        $debtor = Member::query()->create(['name' => 'Alice']);
        $creditor = Member::query()->create(['name' => 'Bob']);

        // Alice owes Bob 15000 from a shared expense.
        $this->seedDebt($debtor, $creditor, 15000);

        $this->post(route('settlements.store'), [
            'from_member_id' => $debtor->id,
            'to_member_id' => $creditor->id,
            'amount' => 20000,
            'settlement_date' => '2026-06-15',
        ])->assertSessionHasErrors('amount');

        $this->assertDatabaseCount('settlements', 0);
    }

    public function test_settlements_index_is_paginated(): void
    {
        $debtor = Member::query()->create(['name' => 'Alice']);
        $creditor = Member::query()->create(['name' => 'Bob']);

        // 16 settlements, ascending dates so latest('settlement_date') ordering is deterministic.
        for ($i = 1; $i <= 16; $i++) {
            Settlement::query()->create([
                'from_member_id' => $debtor->id,
                'to_member_id' => $creditor->id,
                'amount' => 1000,
                'settlement_date' => '2026-01-'.str_pad((string) $i, 2, '0', STR_PAD_LEFT),
                'note' => 'Settlement-'.str_pad((string) $i, 2, '0', STR_PAD_LEFT),
            ]);
        }

        // Page 1 (15 per page) shows newest (Settlement-16), not the oldest (Settlement-01).
        $page1 = $this->get(route('settlements.index'))->assertOk();
        $page1->assertSee('Settlement-16');
        $page1->assertDontSee('Settlement-01');

        // Page 2 shows the oldest.
        $page2 = $this->get(route('settlements.index', ['page' => 2]))->assertOk();
        $page2->assertSee('Settlement-01');
        $page2->assertDontSee('Settlement-16');
    }
}
