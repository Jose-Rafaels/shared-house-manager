<?php

namespace Tests\Feature;

use App\Models\Member;
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
}
