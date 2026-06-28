<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChoreRequest;
use App\Models\Chore;
use App\Models\ChoreAssignment;
use App\Models\Member;
use App\Services\ActivityLogService;

class ChoreController extends Controller
{
    public function index()
    {
        return view('chores.index', [
            'chores' => Chore::query()->with('assignments.member')->latest()->get(),
            'members' => Member::query()->active()->orderBy('name')->get(),
        ]);
    }

    public function store(ChoreRequest $request, ActivityLogService $activityLogService)
    {
        $chore = Chore::query()->create($request->validated());
        $activityLogService->log('chore.created', "Created chore {$chore->name}.", $chore);

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

    public function update(ChoreRequest $request, Chore $chore, ActivityLogService $activityLogService)
    {
        $chore->update($request->validated());
        $activityLogService->log('chore.updated', "Updated chore {$chore->name}.", $chore);

        return back()->with('status', 'Chore updated.');
    }

    public function destroy(Chore $chore, ActivityLogService $activityLogService)
    {
        $chore->delete();
        $activityLogService->log('chore.deleted', "Deleted chore {$chore->name}.", $chore);

        return back()->with('status', 'Chore deleted.');
    }
}
