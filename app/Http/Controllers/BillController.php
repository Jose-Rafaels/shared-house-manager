<?php

namespace App\Http\Controllers;

use App\Http\Requests\BillPaymentRequest;
use App\Http\Requests\BillRequest;
use App\Models\Bill;
use App\Models\BillParticipant;
use App\Models\Housemate;
use App\Services\ActivityLogService;
use App\Services\MoneySplitService;
use Illuminate\Support\Facades\DB;

class BillController extends Controller
{
    public function index()
    {
        return view('bills.index', [
            'bills' => Bill::query()->with(['participants.housemate', 'payments.housemate'])->latest('billing_month')->get(),
            'housemates' => Housemate::active()->orderBy('name')->get(),
        ]);
    }

    public function store(BillRequest $request, MoneySplitService $moneySplitService, ActivityLogService $activityLogService)
    {
        DB::transaction(function () use ($request, $moneySplitService, $activityLogService) {
            $data = $request->validated();
            $housemateIds = array_values($data['housemate_ids']);
            unset($data['housemate_ids']);

            $bill = Bill::query()->create($data);
            $shares = $moneySplitService->splitEvenly((int) $bill->amount, count($housemateIds));

            foreach ($housemateIds as $index => $housemateId) {
                BillParticipant::query()->create([
                    'bill_id' => $bill->id,
                    'housemate_id' => $housemateId,
                    'share_amount' => $shares[$index],
                ]);
            }

            $activityLogService->log('bill.created', "Created bill {$bill->title}.", $bill);
        });

        return back()->with('status', 'Bill created.');
    }

    public function markPaid(BillPaymentRequest $request, Bill $bill, ActivityLogService $activityLogService)
    {
        $payment = $bill->payments()->create($request->validated());
        $activityLogService->log('bill.payment', "Recorded payment for {$bill->title}.", $payment);

        return back()->with('status', 'Payment recorded.');
    }
}
