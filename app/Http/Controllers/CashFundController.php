<?php

namespace App\Http\Controllers;

use App\Http\Requests\CashFundEntryRequest;
use App\Models\CashFundEntry;
use App\Models\Housemate;
use App\Services\ActivityLogService;
use App\Services\CashFundService;

class CashFundController extends Controller
{
    public function index(CashFundService $cashFundService)
    {
        return view('cash-fund.index', [
            'entries' => CashFundEntry::query()->with('housemate')->latest('entry_date')->get(),
            'housemates' => Housemate::active()->orderBy('name')->get(),
            'balance' => $cashFundService->balance(),
        ]);
    }

    public function store(CashFundEntryRequest $request, ActivityLogService $activityLogService)
    {
        $data = $request->validated();

        if ($request->hasFile('receipt')) {
            $data['receipt_path'] = $request->file('receipt')->store('receipts', 'public');
        }

        unset($data['receipt']);

        $entry = CashFundEntry::query()->create($data);
        $activityLogService->log('cash-fund.entry', "Added cash fund {$entry->type} entry {$entry->title}.", $entry);

        return back()->with('status', 'Cash fund entry added.');
    }
}
