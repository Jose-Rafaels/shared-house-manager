<?php

namespace Tests\Feature;

use App\Models\Category;
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

        $expense = \App\Models\Expense::first();

        $this->delete(route('expenses.destroy', $expense))->assertRedirect();

        $this->assertDatabaseMissing('expenses', ['id' => $expense->id]);
        $this->assertDatabaseMissing('expense_splits', ['expense_id' => $expense->id]);
    }
}