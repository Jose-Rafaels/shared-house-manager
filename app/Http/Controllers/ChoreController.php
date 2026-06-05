<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChoreRequest;
use App\Models\Chore;
use App\Models\ChoreAssignment;
use App\Models\Housemate;
use App\Services\ActivityLogService;
use App\Services\ChoreAssignmentService;
use Illuminate\Support\Facades\DB;

class ChoreController extends Controller
{
    public function index()
    {
        return view('chores.index', [
            'chores' => Chore::query()->with(['rotations.housemate', 'assignments.housemate'])->latest()->get(),
            'housemates' => Housemate::active()->orderBy('name')->get(),
        ]);
    }

    public function store(ChoreRequest $request, ChoreAssignmentService $choreAssignmentService, ActivityLogService $activityLogService)
    {
        DB::transaction(function () use ($request, $choreAssignmentService, $activityLogService) {
            $data = $request->validated();
            $housemateIds = array_values($data['housemate_ids']);
            unset($data['housemate_ids']);

            $chore = Chore::query()->create($data);

            foreach ($housemateIds as $index => $housemateId) {
                $chore->rotations()->create([
                    'housemate_id' => $housemateId,
                    'sort_order' => $index + 1,
                ]);
            }

            $choreAssignmentService->ensureAssignments($chore->fresh(), 12);
            $activityLogService->log('chore.created', "Created chore {$chore->name}.", $chore);
        });

        return back()->with('status', 'Chore created.');
    }

    public function complete(ChoreAssignment $assignment, ActivityLogService $activityLogService)
    {
        $assignment->update(['completed_at' => now()]);
        $activityLogService->log('chore.completed', "Marked {$assignment->chore->name} complete.", $assignment);

        return back()->with('status', 'Chore marked complete.');
    }
}
