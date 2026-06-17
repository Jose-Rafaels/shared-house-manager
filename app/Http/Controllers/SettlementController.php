<?php

namespace App\Http\Controllers;

use App\Http\Requests\SettlementRequest;
use App\Models\Member;
use App\Models\Settlement;
use App\Services\ActivityLogService;
use App\Services\DebtLedgerService;

class SettlementController extends Controller
{
    public function index(DebtLedgerService $debtLedgerService)
    {
        return view('settlements.index', [
            'settlements' => Settlement::query()
                ->with(['fromMember', 'toMember'])
                ->latest('settlement_date')
                ->get(),
            'members' => Member::query()->orderBy('name')->get(),
            'outstandingBalances' => $debtLedgerService->outstandingBalances(),
        ]);
    }

    public function store(SettlementRequest $request, ActivityLogService $activityLogService)
    {
        $settlement = Settlement::query()->create($request->validated());
        $activityLogService->log('settlement.created', 'Recorded a settlement.', $settlement);

        return back()->with('status', 'Settlement recorded.');
    }
}
