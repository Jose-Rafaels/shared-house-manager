<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\Settlement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettlementFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_settlement_can_be_recorded(): void
    {
        $debtor = Member::query()->create(['name' => 'Alice']);
        $creditor = Member::query()->create(['name' => 'Bob']);

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

        $settlement = Settlement::query()->create([
            'from_member_id' => $debtor->id,
            'to_member_id' => $creditor->id,
            'amount' => 50000,
            'settlement_date' => '2026-06-15',
        ]);

        $this->put(route('settlements.update', $settlement), [
            'from_member_id' => $debtor->id,
            'to_member_id' => $creditor->id,
            'amount' => 75000,
            'settlement_date' => '2026-06-20',
            'note' => 'Updated amount',
        ])->assertRedirect();

        $this->assertDatabaseHas('settlements', [
            'id' => $settlement->id,
            'amount' => 75000,
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
}
