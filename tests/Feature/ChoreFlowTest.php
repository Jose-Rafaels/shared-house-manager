<?php

namespace Tests\Feature;

use App\Models\Chore;
use App\Models\Member;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChoreFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_chore_can_be_created(): void
    {
        $alice = Member::query()->create(['name' => 'Alice']);

        $this->post(route('chores.store'), [
            'name' => 'Clean bathroom',
            'description' => 'Scrub the tub',
            'assigned_to_member_id' => $alice->id,
        ])->assertRedirect();

        $this->assertDatabaseHas('chores', [
            'name' => 'Clean bathroom',
            'description' => 'Scrub the tub',
            'assigned_to_member_id' => $alice->id,
        ]);
    }

    public function test_chore_can_be_updated(): void
    {
        $alice = Member::query()->create(['name' => 'Alice']);
        $bob = Member::query()->create(['name' => 'Bob']);

        $this->post(route('chores.store'), [
            'name' => 'Clean bathroom',
            'assigned_to_member_id' => $alice->id,
        ])->assertRedirect();

        $chore = Chore::first();

        $this->put(route('chores.update', $chore), [
            'name' => 'Clean kitchen',
            'description' => 'Wash dishes',
            'assigned_to_member_id' => $bob->id,
        ])->assertRedirect();

        $this->assertDatabaseHas('chores', [
            'id' => $chore->id,
            'name' => 'Clean kitchen',
            'description' => 'Wash dishes',
            'assigned_to_member_id' => $bob->id,
        ]);
    }

    public function test_chore_can_be_deleted(): void
    {
        $alice = Member::query()->create(['name' => 'Alice']);

        $this->post(route('chores.store'), [
            'name' => 'Clean bathroom',
            'assigned_to_member_id' => $alice->id,
        ])->assertRedirect();

        $chore = Chore::first();

        $this->delete(route('chores.destroy', $chore))->assertRedirect();

        $this->assertDatabaseMissing('chores', ['id' => $chore->id]);
    }
}
