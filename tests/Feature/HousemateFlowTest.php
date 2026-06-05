<?php

namespace Tests\Feature;

use App\Models\Housemate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HousemateFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_housemate_can_be_created_and_archived(): void
    {
        $this->post(route('housemates.store'), [
            'name' => 'Mahdy',
        ])->assertRedirect();

        $housemate = Housemate::query()->firstOrFail();
        $this->assertSame('Mahdy', $housemate->name);

        $this->patch(route('housemates.archive', $housemate))
            ->assertRedirect();

        $this->assertNotNull($housemate->fresh()->archived_at);
    }
}
