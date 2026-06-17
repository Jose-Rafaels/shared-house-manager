<?php

namespace App\Http\Controllers;

use App\Http\Requests\ShoppingItemRequest;
use App\Http\Requests\ShoppingPurchaseRequest;
use App\Models\Member;
use App\Models\ShoppingItem;
use App\Services\ActivityLogService;

class ShoppingController extends Controller
{
    public function index()
    {
        return view('shopping.index', [
            'items' => ShoppingItem::query()->with(['addedBy', 'purchases.purchasedBy'])->latest()->get(),
            'members' => Member::query()->orderBy('name')->get(),
        ]);
    }

    public function store(ShoppingItemRequest $request, ActivityLogService $activityLogService)
    {
        $item = ShoppingItem::query()->create($request->validated());
        $activityLogService->log('shopping.item', "Added shopping item {$item->name}.", $item);

        return back()->with('status', 'Shopping item added.');
    }

    public function markPurchased(ShoppingPurchaseRequest $request, ShoppingItem $shoppingItem, ActivityLogService $activityLogService)
    {
        $shoppingItem->update(['purchased_at' => $request->date('purchased_on')]);
        $purchase = $shoppingItem->purchases()->create($request->validated());
        $activityLogService->log('shopping.purchase', "Marked {$shoppingItem->name} purchased.", $purchase);

        return back()->with('status', 'Shopping item marked purchased.');
    }
}
