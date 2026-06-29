<?php

namespace Tests\Feature;

use App\Models\Member;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemberFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_can_be_archived(): void
    {
        $this->post(route('members.store'), [
            'name' => 'Alice',
        ])->assertRedirect();

        $this->assertDatabaseHas('members', ['name' => 'Alice', 'deleted_at' => null]);

        $member = Member::query()->first();
        $this->delete(route('members.destroy', $member))->assertRedirect();

        $this->assertSoftDeleted('members', ['id' => $member->id]);
    }

    public function test_member_with_history_can_be_archived_without_fk_error(): void
    {
        $alice = Member::query()->create(['name' => 'Alice']);
        $bob = Member::query()->create(['name' => 'Bob']);

        \App\Models\Expense::query()->create([
            'payer_id' => $alice->id,
            'category_id' => null,
            'amount' => 30000,
            'description' => 'Groceries',
            'expense_date' => '2026-06-01',
        ])->splits()->createMany([
            ['member_id' => $bob->id, 'amount_owed' => 15000],
            ['member_id' => $alice->id, 'amount_owed' => 15000],
        ]);

        // Should not throw — soft delete keeps row, FK references stay valid.
        $this->delete(route('members.destroy', $alice))->assertRedirect();
        $this->assertSoftDeleted('members', ['id' => $alice->id]);
    }
}
