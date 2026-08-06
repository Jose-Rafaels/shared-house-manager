<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Expense;
use App\Models\Member;
use App\Services\DashboardService;
use App\Services\DebtLedgerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardServiceTest extends TestCase
{
    use RefreshDatabase;

    private DashboardService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new DashboardService(new DebtLedgerService);
    }

    public function test_summary_null_month_means_all_months(): void
    {
        $payer = Member::query()->create(['name' => 'Alice']);

        Expense::query()->create([
            'payer_id' => $payer->id, 'category_id' => null,
            'amount' => 20000, 'description' => 'June',
            'expense_date' => '2026-06-10',
        ]);
        Expense::query()->create([
            'payer_id' => $payer->id, 'category_id' => null,
            'amount' => 30000, 'description' => 'July',
            'expense_date' => '2026-07-10',
        ]);

        $summary = $this->service->summary(null);

        $this->assertCount(2, $summary['currentExpenses']);
        $this->assertSame(50000, $summary['expensesTotal']);
    }

    public function test_summary_can_filter_by_a_specific_month(): void
    {
        $payer = Member::query()->create(['name' => 'Alice']);

        Expense::query()->create([
            'payer_id' => $payer->id, 'category_id' => null,
            'amount' => 20000, 'description' => 'June',
            'expense_date' => '2026-06-10',
        ]);
        Expense::query()->create([
            'payer_id' => $payer->id, 'category_id' => null,
            'amount' => 30000, 'description' => 'July',
            'expense_date' => '2026-07-10',
        ]);

        $summary = $this->service->summary('2026-06');

        $this->assertCount(1, $summary['currentExpenses']);
        $this->assertSame('June', $summary['currentExpenses']->first()->description);
        $this->assertSame(20000, $summary['expensesTotal']);
    }

    public function test_summary_can_filter_by_category(): void
    {
        $payer = Member::query()->create(['name' => 'Alice']);
        $food = Category::query()->create(['name' => 'Makanan']);
        $bills = Category::query()->create(['name' => 'Tagihan']);

        Expense::query()->create([
            'payer_id' => $payer->id, 'category_id' => $food->id,
            'amount' => 15000, 'description' => 'Food',
            'expense_date' => '2026-06-10',
        ]);
        Expense::query()->create([
            'payer_id' => $payer->id, 'category_id' => $bills->id,
            'amount' => 40000, 'description' => 'Bills',
            'expense_date' => '2026-06-10',
        ]);

        $summary = $this->service->summary('2026-06', $food->id);

        $this->assertCount(1, $summary['currentExpenses']);
        $this->assertSame(15000, $summary['expensesTotal']);
    }

    public function test_category_breakdown_groups_by_category_for_the_month(): void
    {
        $payer = Member::query()->create(['name' => 'Alice']);
        $food = Category::query()->create(['name' => 'Makanan']);
        $bills = Category::query()->create(['name' => 'Tagihan']);

        Expense::query()->create([
            'payer_id' => $payer->id, 'category_id' => $food->id,
            'amount' => 15000, 'description' => 'Food1', 'expense_date' => '2026-06-10',
        ]);
        Expense::query()->create([
            'payer_id' => $payer->id, 'category_id' => $food->id,
            'amount' => 5000, 'description' => 'Food2', 'expense_date' => '2026-06-12',
        ]);
        Expense::query()->create([
            'payer_id' => $payer->id, 'category_id' => $bills->id,
            'amount' => 40000, 'description' => 'Bills', 'expense_date' => '2026-06-10',
        ]);
        // Different month — excluded from breakdown
        Expense::query()->create([
            'payer_id' => $payer->id, 'category_id' => $food->id,
            'amount' => 99999, 'description' => 'OldFood', 'expense_date' => '2026-05-10',
        ]);

        $summary = $this->service->summary('2026-06');

        $breakdown = $summary['categoryBreakdown'];
        $this->assertCount(2, $breakdown);

        $foodRow = $breakdown->first(fn ($r) => $r->category_id === $food->id);
        $this->assertSame(20000, (int) $foodRow->total);
        $this->assertSame(2, (int) $foodRow->count);

        $billsRow = $breakdown->first(fn ($r) => $r->category_id === $bills->id);
        $this->assertSame(40000, (int) $billsRow->total);
        $this->assertSame(1, (int) $billsRow->count);
    }

    public function test_category_breakdown_ignores_category_filter(): void
    {
        $payer = Member::query()->create(['name' => 'Alice']);
        $food = Category::query()->create(['name' => 'Makanan']);
        $bills = Category::query()->create(['name' => 'Tagihan']);

        Expense::query()->create([
            'payer_id' => $payer->id, 'category_id' => $food->id,
            'amount' => 15000, 'description' => 'Food', 'expense_date' => '2026-06-10',
        ]);
        Expense::query()->create([
            'payer_id' => $payer->id, 'category_id' => $bills->id,
            'amount' => 40000, 'description' => 'Bills', 'expense_date' => '2026-06-10',
        ]);

        // Filter cards by food, but breakdown still shows both categories.
        $summary = $this->service->summary('2026-06', $food->id);

        $this->assertCount(2, $summary['categoryBreakdown']);
    }
}