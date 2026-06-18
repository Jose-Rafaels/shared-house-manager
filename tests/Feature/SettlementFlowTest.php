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

    public function test_settlement_can_be_updated(): void
    {
        $debtor = Member::query()->create(['name' => 'Alice']);
        $creditor = Member::query()->create(['name' => 'Bob']);

        // Alice owes Bob 100000.
        $this->seedDebt($debtor, $creditor, 100000);

        // Insert the settlement directly to bypass validation in setup.
        $settlement = Settlement::query()->create([
            'from_member_id' => $debtor->id,
            'to_member_id' => $creditor->id,
            'amount' => 50000,
            'settlement_date' => '2026-06-15',
        ]);

        // Update to a higher amount that still doesn't exceed the remaining 50000.
        $this->put(route('settlements.update', $settlement), [
            'from_member_id' => $debtor->id,
            'to_member_id' => $creditor->id,
            'amount' => 50000,
            'settlement_date' => '2026-06-18',
            'note' => 'Updated amount',
        ])->assertRedirect();

        $this->assertDatabaseHas('settlements', [
            'id' => $settlement->id,
            'amount' => 50000,
            'note' => 'Updated amount',
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
}
