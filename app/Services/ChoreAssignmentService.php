<?php

namespace App\Services;

use App\Models\Chore;
use App\Models\ChoreAssignment;
use Carbon\Carbon;

class ChoreAssignmentService
{
    /**
     * Ensure the next $weeksAhead assignments exist for this chore, anchored
     * to the current week. Rotation offset is computed from rotation_start_date
     * so the assigned member continues from the rotation's phase, not week 0.
     */
    public function ensureAssignments(Chore $chore, int $weeksAhead = 8): void
    {
        $rotations = $chore->rotations()->orderBy('sort_order')->get();

        if ($rotations->isEmpty()) {
            return;
        }

        $rotationCount = $rotations->count();
        $startDate = Carbon::parse($chore->rotation_start_date)->startOfWeek();
        $thisWeek = Carbon::now()->startOfWeek();

        // Number of weeks elapsed since the rotation started (floor at 0).
        $weeksElapsed = max(0, (int) $startDate->diffInWeeks($thisWeek));

        $currentOffset = $weeksElapsed % $rotationCount;

        for ($i = 0; $i < $weeksAhead; $i++) {
            $assignedFor = $thisWeek->copy()->addWeeks($i);
            $rotation = $rotations[($currentOffset + $i) % $rotationCount];

            ChoreAssignment::query()->firstOrCreate(
                [
                    'chore_id' => $chore->id,
                    'assigned_for_date' => $assignedFor->toDateString(),
                ],
                [
                    'member_id' => $rotation->member_id,
                ],
            );
        }
    }
}
