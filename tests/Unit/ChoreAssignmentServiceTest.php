<?php

namespace Tests\Unit;

use App\Models\Chore;
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
}
