<?php

namespace App\Http\Controllers;

use App\Http\Requests\HousemateRequest;
use App\Models\Housemate;
use App\Services\ActivityLogService;

class HousemateController extends Controller
{
    public function index()
    {
        return view('housemates.index', [
            'housemates' => Housemate::query()->latest()->get(),
        ]);
    }

    public function store(HousemateRequest $request, ActivityLogService $activityLogService)
    {
        $housemate = Housemate::query()->create($request->validated());
        $activityLogService->log('housemate.created', "Added housemate {$housemate->name}.", $housemate);

        return back()->with('status', 'Housemate added.');
    }

    public function update(HousemateRequest $request, Housemate $housemate, ActivityLogService $activityLogService)
    {
        $housemate->update($request->validated());
        $activityLogService->log('housemate.updated', "Updated housemate {$housemate->name}.", $housemate);

        return back()->with('status', 'Housemate updated.');
    }

    public function archive(Housemate $housemate, ActivityLogService $activityLogService)
    {
        $housemate->update(['archived_at' => now()]);
        $activityLogService->log('housemate.archived', "Archived housemate {$housemate->name}.", $housemate);

        return back()->with('status', 'Housemate archived.');
    }
}
