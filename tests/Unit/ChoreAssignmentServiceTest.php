<?php

namespace Tests\Unit;

use App\Models\Chore;
use App\Models\ChoreAssignment;
use App\Models\Member;
use App\Services\ChoreAssignmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChoreAssignmentServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_assignments_rotate_in_order(): void
    {
        $first = Member::query()->create(['name' => 'First']);
        $second = Member::query()->create(['name' => 'Second']);

        $chore = Chore::query()->create([
            'name' => 'Clean Kitchen',
            'rotation_start_date' => now()->startOfWeek()->toDateString(),
        ]);

        $chore->rotations()->createMany([
            ['member_id' => $first->id, 'sort_order' => 1],
            ['member_id' => $second->id, 'sort_order' => 2],
        ]);

        app(ChoreAssignmentService::class)->ensureAssignments($chore->fresh(), 3);

        $assignments = $chore->fresh()->assignments()->orderBy('assigned_for_date')->get();

        $this->assertSame([$first->id, $second->id, $first->id], $assignments->pluck('member_id')->take(3)->all());
    }

    public function test_assignments_anchor_to_current_week_when_start_date_in_past(): void
    {
        $first = Member::query()->create(['name' => 'First']);
        $second = Member::query()->create(['name' => 'Second']);
        $third = Member::query()->create(['name' => 'Third']);

        // Start 10 weeks ago. With 3 members in rotation, currentOffset = 10 % 3 = 1 → second member.
        $chore = Chore::query()->create([
            'name' => 'Take out trash',
            'rotation_start_date' => now()->subWeeks(10)->startOfWeek()->toDateString(),
        ]);

        $chore->rotations()->createMany([
            ['member_id' => $first->id, 'sort_order' => 1],
            ['member_id' => $second->id, 'sort_order' => 2],
            ['member_id' => $third->id, 'sort_order' => 3],
        ]);

        app(ChoreAssignmentService::class)->ensureAssignments($chore->fresh(), 4);

        $assignments = ChoreAssignment::query()
            ->where('chore_id', $chore->id)
            ->orderBy('assigned_for_date')
            ->get();

        // Anchor: this week starts at $second, then $third, $first, $second.
        $this->assertSame(
            [$second->id, $third->id, $first->id, $second->id],
            $assignments->pluck('member_id')->take(4)->all()
        );

        // No assignment should be in the past.
        $this->assertTrue(
            $assignments->every(fn ($a) => $a->assigned_for_date->gte(now()->startOfWeek()->toDateString()))
        );
    }
}
