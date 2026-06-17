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
}
