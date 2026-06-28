<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Expense;
use App\Models\Member;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpenseFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_expense_can_be_created_with_splits(): void
    {
        $payer = Member::query()->create(['name' => 'Alice']);
        $memberB = Member::query()->create(['name' => 'Bob']);
        $category = Category::query()->create(['name' => 'Listrik']);

        $this->post(route('expenses.store'), [
            'description' => 'Listrik bulan ini',
            'payer_id' => $payer->id,
            'category_id' => $category->id,
            'amount' => 100000,
            'expense_date' => '2026-06-01',
            'member_ids' => [$payer->id, $memberB->id],
        ])->assertRedirect();

        $this->assertDatabaseHas('expenses', [
            'description' => 'Listrik bulan ini',
            'payer_id' => $payer->id,
            'amount' => 100000,
        ]);
        $this->assertDatabaseHas('expense_splits', [
            'member_id' => $payer->id,
            'amount_owed' => 50000,
        ]);
        $this->assertDatabaseHas('expense_splits', [
            'member_id' => $memberB->id,
            'amount_owed' => 50000,
        ]);
    }

    public function test_expense_can_be_updated_with_synced_splits(): void
    {
        $payer = Member::query()->create(['name' => 'Alice']);
        $memberB = Member::query()->create(['name' => 'Bob']);
        $category = Category::query()->create(['name' => 'Listrik']);

        $this->post(route('expenses.store'), [
            'description' => 'Listrik bulan ini',
            'payer_id' => $payer->id,
            'category_id' => $category->id,
            'amount' => 100000,
            'expense_date' => '2026-06-01',
            'member_ids' => [$payer->id, $memberB->id],
        ])->assertRedirect();

        $expense = Expense::first();

        $memberC = Member::query()->create(['name' => 'Charlie']);

        $this->put(route('expenses.update', $expense), [
            'description' => 'Listrik bulan lalu',
            'payer_id' => $payer->id,
            'category_id' => $category->id,
            'amount' => 150000,
            'expense_date' => '2026-06-01',
            'member_ids' => [$payer->id, $memberB->id, $memberC->id],
        ])->assertRedirect();

        $this->assertDatabaseHas('expenses', [
            'id' => $expense->id,
            'description' => 'Listrik bulan lalu',
            'amount' => 150000,
        ]);

        $this->assertEquals(3, $expense->fresh()->splits()->count());
    }

    public function test_expense_can_be_deleted(): void
    {
        $payer = Member::query()->create(['name' => 'Alice']);
        $memberB = Member::query()->create(['name' => 'Bob']);

        $this->post(route('expenses.store'), [
            'description' => 'Test expense',
            'payer_id' => $payer->id,
            'amount' => 50000,
            'expense_date' => '2026-06-01',
            'member_ids' => [$payer->id, $memberB->id],
        ])->assertRedirect();

        $expense = Expense::first();

        $this->delete(route('expenses.destroy', $expense))->assertRedirect();

        $this->assertDatabaseMissing('expenses', ['id' => $expense->id]);
        $this->assertDatabaseMissing('expense_splits', ['expense_id' => $expense->id]);
    }

    public function test_expense_split_can_be_toggled_settled(): void
    {
        $payer = Member::query()->create(['name' => 'Alice']);
        $memberB = Member::query()->create(['name' => 'Bob']);

        $this->post(route('expenses.store'), [
            'description' => 'Test',
            'payer_id' => $payer->id,
            'amount' => 50000,
            'expense_date' => '2026-06-01',
            'member_ids' => [$payer->id, $memberB->id],
        ])->assertRedirect();

        $split = Expense::first()->splits->firstWhere('member_id', $memberB->id);

        $this->patch(route('expense-splits.toggle', $split), ['is_settled' => 1])
            ->assertRedirect();

        $this->assertTrue($split->fresh()->is_settled);
        $this->assertDatabaseHas('activity_logs', [
            'action' => 'expense_split.settled',
            'subject_type' => \App\Models\ExpenseSplit::class,
            'subject_id' => $split->id,
        ]);
    }

    public function test_fully_settled_expense_cannot_be_edited_or_deleted(): void
    {
        $payer = Member::query()->create(['name' => 'Alice']);
        $memberB = Member::query()->create(['name' => 'Bob']);

        $this->post(route('expenses.store'), [
            'description' => 'Test',
            'payer_id' => $payer->id,
            'amount' => 50000,
            'expense_date' => '2026-06-01',
            'member_ids' => [$payer->id, $memberB->id],
        ])->assertRedirect();

        $expense = Expense::first();
        $expense->splits()->update(['is_settled' => true]);

        $this->put(route('expenses.update', $expense), [
            'description' => 'New',
            'payer_id' => $payer->id,
            'amount' => 99999,
            'expense_date' => '2026-06-01',
            'member_ids' => [$payer->id, $memberB->id],
        ])->assertRedirect()->assertSessionHas('error');

        $this->delete(route('expenses.destroy', $expense))
            ->assertRedirect()->assertSessionHas('error');

        $this->assertDatabaseHas('expenses', ['id' => $expense->id, 'amount' => 50000]);
    }

    public function test_partially_settled_expense_can_still_be_edited(): void
    {
        $payer = Member::query()->create(['name' => 'Alice']);
        $memberB = Member::query()->create(['name' => 'Bob']);

        $this->post(route('expenses.store'), [
            'description' => 'Test',
            'payer_id' => $payer->id,
            'amount' => 50000,
            'expense_date' => '2026-06-01',
            'member_ids' => [$payer->id, $memberB->id],
        ])->assertRedirect();

        $expense = Expense::first();
        $expense->splits()->firstWhere('member_id', $memberB->id)->update(['is_settled' => true]);

        $this->put(route('expenses.update', $expense), [
            'description' => 'Updated',
            'payer_id' => $payer->id,
            'amount' => 75000,
            'expense_date' => '2026-06-01',
            'member_ids' => [$payer->id, $memberB->id],
        ])->assertRedirect()->assertSessionMissing('error');

        $this->assertSame('Updated', $expense->fresh()->description);
    }

    public function test_fully_settled_expense_hides_edit_button_in_view(): void
    {
        $payer = Member::query()->create(['name' => 'Alice']);
        $memberB = Member::query()->create(['name' => 'Bob']);

        $this->post(route('expenses.store'), [
            'description' => 'Test',
            'payer_id' => $payer->id,
            'amount' => 50000,
            'expense_date' => '2026-06-01',
            'member_ids' => [$payer->id, $memberB->id],
        ])->assertRedirect();

        $expense = Expense::first();
        $expense->splits()->update(['is_settled' => true]);

        $response = $this->get(route('expenses.index'));
        $response->assertSee('bg-emerald-100', false);
        $this->assertTrue(
            ! str_contains($response->getContent(), 'showModal()" class="text-sm font-medium text-slate-700 hover:text-slate-900">'),
            'Edit button should not render for fully-settled expense rows.',
        );
    }
}
