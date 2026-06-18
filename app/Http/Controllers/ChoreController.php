<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChoreRequest;
use App\Models\Chore;
use App\Models\ChoreAssignment;
use App\Models\Member;
use App\Services\ActivityLogService;
use App\Services\ChoreAssignmentService;
use Illuminate\Support\Facades\DB;

class ChoreController extends Controller
{
    public function index()
    {
        return view('chores.index', [
            'chores' => Chore::query()->with(['rotations.member', 'assignments.member'])->latest()->get(),
            'members' => Member::query()->active()->orderBy('name')->get(),
        ]);
    }

    public function store(ChoreRequest $request, ChoreAssignmentService $choreAssignmentService, ActivityLogService $activityLogService)
    {
        DB::transaction(function () use ($request, $choreAssignmentService, $activityLogService) {
            $data = $request->validated();
            $memberIds = array_values($data['member_ids']);
            unset($data['member_ids']);

            $chore = Chore::query()->create($data);

            foreach ($memberIds as $index => $memberId) {
                $chore->rotations()->create([
                    'member_id' => $memberId,
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
        abort_if(
            $assignment->completed_at !== null,
            422,
            'Chore already completed.'
        );

        $assignment->update(['completed_at' => now()]);
        $activityLogService->log('chore.completed', "Marked {$assignment->chore->name} complete.", $assignment);

        return back()->with('status', 'Chore marked complete.');
    }

    public function update(ChoreRequest $request, Chore $chore, ChoreAssignmentService $choreAssignmentService, ActivityLogService $activityLogService)
    {
        DB::transaction(function () use ($request, $chore, $choreAssignmentService, $activityLogService) {
            $data = $request->validated();
            $memberIds = array_values($data['member_ids']);
            unset($data['member_ids']);

            $chore->update($data);
            $chore->rotations()->delete();

            foreach ($memberIds as $index => $memberId) {
                $chore->rotations()->create([
                    'member_id' => $memberId,
                    'sort_order' => $index + 1,
                ]);
            }

            // Delete only future, incomplete assignments so they get regenerated
            // with the new rotation. Past history is preserved.
            $chore->assignments()
                ->whereNull('completed_at')
                ->whereDate('assigned_for_date', '>=', now()->startOfWeek()->toDateString())
                ->delete();

            $choreAssignmentService->ensureAssignments($chore->fresh(), 12);
            $activityLogService->log('chore.updated', "Updated chore {$chore->name}.", $chore);
        });

        return back()->with('status', 'Chore updated.');
    }

    public function destroy(Chore $chore, ActivityLogService $activityLogService)
    {
        $chore->delete();
        $activityLogService->log('chore.deleted', "Deleted chore {$chore->name}.", $chore);

        return back()->with('status', 'Chore deleted.');
    }
}
