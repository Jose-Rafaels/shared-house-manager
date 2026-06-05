<?php

namespace Tests\Unit;

use App\Models\Chore;
use App\Models\Housemate;
use App\Services\ChoreAssignmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChoreAssignmentServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_assignments_rotate_in_order(): void
    {
        $first = Housemate::query()->create(['name' => 'First']);
        $second = Housemate::query()->create(['name' => 'Second']);

        $chore = Chore::query()->create([
            'name' => 'Clean Kitchen',
            'rotation_start_date' => now()->startOfWeek()->toDateString(),
        ]);

        $chore->rotations()->createMany([
            ['housemate_id' => $first->id, 'sort_order' => 1],
            ['housemate_id' => $second->id, 'sort_order' => 2],
        ]);

        app(ChoreAssignmentService::class)->ensureAssignments($chore->fresh(), 3);

        $assignments = $chore->fresh()->assignments()->orderBy('assigned_for_date')->get();

        $this->assertSame([$first->id, $second->id, $first->id], $assignments->pluck('housemate_id')->take(3)->all());
    }
}
