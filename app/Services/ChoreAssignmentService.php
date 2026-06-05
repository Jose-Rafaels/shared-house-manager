<?php

namespace App\Services;

use App\Models\Chore;
use App\Models\ChoreAssignment;
use Carbon\Carbon;

class ChoreAssignmentService
{
    public function ensureAssignments(Chore $chore, int $weeksAhead = 8): void
    {
        $rotations = $chore->rotations()->get();

        if ($rotations->isEmpty()) {
            return;
        }

        $startDate = Carbon::parse($chore->rotation_start_date)->startOfWeek();

        for ($offset = 0; $offset < $weeksAhead; $offset++) {
            $assignedFor = $startDate->copy()->addWeeks($offset);
            $rotation = $rotations[$offset % $rotations->count()];

            ChoreAssignment::query()->firstOrCreate(
                [
                    'chore_id' => $chore->id,
                    'assigned_for_date' => $assignedFor->toDateString(),
                ],
                [
                    'housemate_id' => $rotation->housemate_id,
                ],
            );
        }
    }
}
