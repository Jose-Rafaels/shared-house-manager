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
        $bob = Member::query()->create(['name' => 'Bob']);

        $this->post(route('chores.store'), [
            'name' => 'Clean bathroom',
            'description' => 'Scrub the tub',
            'rotation_start_date' => '2026-06-01',
            'member_ids' => [$alice->id, $bob->id],
        ])->assertRedirect();

        $this->assertDatabaseHas('chores', ['name' => 'Clean bathroom']);
        $this->assertEquals(2, Chore::first()->rotations()->count());
    }

    public function test_chore_can_be_updated_with_synced_rotations(): void
    {
        $alice = Member::query()->create(['name' => 'Alice']);
        $bob = Member::query()->create(['name' => 'Bob']);

        $this->post(route('chores.store'), [
            'name' => 'Clean bathroom',
            'description' => 'Scrub the tub',
            'rotation_start_date' => '2026-06-01',
            'member_ids' => [$alice->id, $bob->id],
        ])->assertRedirect();

        $chore = Chore::first();
        $charlie = Member::query()->create(['name' => 'Charlie']);

        $this->put(route('chores.update', $chore), [
            'name' => 'Clean kitchen',
            'description' => 'Wash dishes',
            'rotation_start_date' => '2026-06-01',
            'member_ids' => [$alice->id, $bob->id, $charlie->id],
        ])->assertRedirect();

        $this->assertDatabaseHas('chores', [
            'id' => $chore->id,
            'name' => 'Clean kitchen',
        ]);
        $this->assertEquals(3, $chore->fresh()->rotations()->count());
    }

    public function test_chore_can_be_deleted(): void
    {
        $alice = Member::query()->create(['name' => 'Alice']);

        $this->post(route('chores.store'), [
            'name' => 'Clean bathroom',
            'rotation_start_date' => '2026-06-01',
            'member_ids' => [$alice->id],
        ])->assertRedirect();

        $chore = Chore::first();

        $this->delete(route('chores.destroy', $chore))->assertRedirect();

        $this->assertDatabaseMissing('chores', ['id' => $chore->id]);
    }
}
