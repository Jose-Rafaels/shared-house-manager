<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Expense;
use App\Models\Member;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_defaults_to_current_month(): void
    {
        $payer = Member::query()->create(['name' => 'Alice']);

        Expense::query()->create([
            'payer_id' => $payer->id, 'category_id' => null,
            'amount' => 20000, 'description' => 'Now',
            'expense_date' => now()->startOfMonth()->toDateString(),
        ]);
        Expense::query()->create([
            'payer_id' => $payer->id, 'category_id' => null,
            'amount' => 50000, 'description' => 'Old',
            'expense_date' => now()->subMonthsNoOverflow(2)->startOfMonth()->toDateString(),
        ]);

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee(number_format(20000))
            ->assertDontSee(number_format(50000));
    }

    public function test_dashboard_filters_by_month_query_param(): void
    {
        $payer = Member::query()->create(['name' => 'Alice']);

        Expense::query()->create([
            'payer_id' => $payer->id, 'category_id' => null,
            'amount' => 20000, 'description' => 'June', 'expense_date' => '2026-06-10',
        ]);
        Expense::query()->create([
            'payer_id' => $payer->id, 'category_id' => null,
            'amount' => 30000, 'description' => 'July', 'expense_date' => '2026-07-10',
        ]);

        $this->get(route('dashboard', ['month' => '2026-06']))
            ->assertOk()
            ->assertSee(number_format(20000))
            ->assertDontSee(number_format(30000));
    }

    public function test_dashboard_all_months_via_all_sentinel(): void
    {
        $payer = Member::query()->create(['name' => 'Alice']);

        Expense::query()->create([
            'payer_id' => $payer->id, 'category_id' => null,
            'amount' => 20000, 'description' => 'June', 'expense_date' => '2026-06-10',
        ]);
        Expense::query()->create([
            'payer_id' => $payer->id, 'category_id' => null,
            'amount' => 30000, 'description' => 'July', 'expense_date' => '2026-07-10',
        ]);

        $this->get(route('dashboard', ['month' => 'all']))
            ->assertOk()
            ->assertSee(number_format(50000));
    }

    public function test_dashboard_invalid_month_falls_back_to_current_month(): void
    {
        $payer = Member::query()->create(['name' => 'Alice']);

        Expense::query()->create([
            'payer_id' => $payer->id, 'category_id' => null,
            'amount' => 20000, 'description' => 'Now',
            'expense_date' => now()->startOfMonth()->toDateString(),
        ]);
        Expense::query()->create([
            'payer_id' => $payer->id, 'category_id' => null,
            'amount' => 50000, 'description' => 'Old',
            'expense_date' => now()->subMonthsNoOverflow(2)->startOfMonth()->toDateString(),
        ]);

        $this->get(route('dashboard', ['month' => 'not-a-month']))
            ->assertOk()
            ->assertSee(number_format(20000))
            ->assertDontSee(number_format(50000));
    }

    public function test_dashboard_renders_all_categories_dropdown(): void
    {
        Category::query()->create(['name' => 'Makanan']);
        Category::query()->create(['name' => 'Tagihan']);

        $this->get(route('dashboard'))->assertOk()->assertSee('Makanan')->assertSee('Tagihan');
    }

    public function test_dashboard_shows_category_breakdown(): void
    {
        $payer = Member::query()->create(['name' => 'Alice']);
        $food = Category::query()->create(['name' => 'Makanan']);

        Expense::query()->create([
            'payer_id' => $payer->id, 'category_id' => $food->id,
            'amount' => 15000, 'description' => 'Food', 'expense_date' => '2026-06-10',
        ]);

        $this->get(route('dashboard', ['month' => '2026-06']))
            ->assertOk()
            ->assertSee('Makanan')
            ->assertSee(number_format(15000));
    }
}
