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

    public function test_expenses_index_is_paginated(): void
    {
        $payer = Member::query()->create(['name' => 'Alice']);
        $memberB = Member::query()->create(['name' => 'Bob']);

        // 16 expenses, ascending dates so latest('expense_date') ordering is deterministic.
        for ($i = 1; $i <= 16; $i++) {
            \App\Models\Expense::query()->create([
                'payer_id' => $payer->id,
                'category_id' => null,
                'amount' => 10000,
                'description' => 'Expense-'.str_pad((string) $i, 2, '0', STR_PAD_LEFT),
                'expense_date' => '2026-01-'.str_pad((string) $i, 2, '0', STR_PAD_LEFT),
            ])->splits()->create([
                'member_id' => $memberB->id,
                'amount_owed' => 10000,
            ]);
        }

        // Page 1 (15 per page) shows newest (Expense-16), not the oldest (Expense-01).
        $page1 = $this->get(route('expenses.index'))->assertOk();
        $page1->assertSee('Expense-16');
        $page1->assertDontSee('Expense-01');

        // Page 2 shows the oldest.
        $page2 = $this->get(route('expenses.index', ['page' => 2]))->assertOk();
        $page2->assertSee('Expense-01');
        $page2->assertDontSee('Expense-16');
    }
}
