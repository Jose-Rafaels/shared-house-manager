<?php

namespace Tests\Unit;

use App\Models\CashFundEntry;
use App\Services\CashFundService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CashFundServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_balance_is_contributions_minus_expenses(): void
    {
        CashFundEntry::query()->create([
            'type' => 'contribution',
            'title' => 'Monthly',
            'amount' => 50000,
            'entry_date' => now()->toDateString(),
        ]);

        CashFundEntry::query()->create([
            'type' => 'expense',
            'title' => 'Detergent',
            'amount' => 15000,
            'entry_date' => now()->toDateString(),
        ]);

        $this->assertSame(35000, app(CashFundService::class)->balance());
    }
}
