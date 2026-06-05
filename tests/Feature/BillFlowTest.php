<?php

namespace Tests\Feature;

use App\Models\Bill;
use App\Models\Housemate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BillFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_bill_creation_splits_amount_evenly(): void
    {
        $housemates = collect(['Mahdy', 'A', 'B'])->map(
            fn (string $name) => Housemate::query()->create(['name' => $name])
        );

        $this->post(route('bills.store'), [
            'title' => 'Internet Bill',
            'type' => 'Internet',
            'amount' => 1000,
            'billing_month' => now()->startOfMonth()->toDateString(),
            'due_date' => now()->addWeek()->toDateString(),
            'housemate_ids' => $housemates->pluck('id')->all(),
        ])->assertRedirect();

        $bill = Bill::query()->with('participants')->firstOrFail();
        $this->assertSame([334, 333, 333], $bill->participants->pluck('share_amount')->sortDesc()->values()->all());
    }
}
