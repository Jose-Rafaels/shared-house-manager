<?php

namespace App\Http\Controllers;

use App\Http\Requests\SettlementRequest;
use App\Models\Member;
use App\Models\Settlement;
use App\Services\ActivityLogService;
use App\Services\DebtLedgerService;
use Illuminate\Support\Facades\DB;

class SettlementController extends Controller
{
    public function index(DebtLedgerService $debtLedgerService)
    {
        return view('settlements.index', [
            'settlements' => Settlement::query()
                ->with(['fromMember', 'toMember'])
                ->latest('settlement_date')
                ->get(),
            'members' => Member::query()->active()->orderBy('name')->get(),
            'outstandingBalances' => $debtLedgerService->outstandingBalances(),
        ]);
    }

    public function store(SettlementRequest $request, ActivityLogService $activityLogService)
    {
        DB::transaction(function () use ($request, $activityLogService) {
            $settlement = Settlement::query()->create($request->validated());
            $activityLogService->log('settlement.created', 'Recorded a settlement.', $settlement);
        });

        return back()->with('status', 'Settlement recorded.');
    }

    public function update(SettlementRequest $request, Settlement $settlement, ActivityLogService $activityLogService)
    {
        DB::transaction(function () use ($request, $settlement, $activityLogService) {
            $settlement->update($request->validated());
            $activityLogService->log('settlement.updated', "Updated settlement #{$settlement->id}.", $settlement);
        });

        return back()->with('status', 'Settlement updated.');
    }

    public function destroy(Settlement $settlement, ActivityLogService $activityLogService)
    {
        $settlement->delete();
        $activityLogService->log('settlement.deleted', "Deleted settlement #{$settlement->id}.", $settlement);

        return back()->with('status', 'Settlement deleted.');
    }
}
