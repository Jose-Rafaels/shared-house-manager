<?php

namespace App\Http\Controllers;

use App\Http\Requests\ShoppingItemRequest;
use App\Models\Member;
use App\Models\ShoppingItem;
use App\Services\ActivityLogService;

class ShoppingController extends Controller
{
    public function index()
    {
        return view('shopping.index', [
            'items' => ShoppingItem::query()->with('addedBy')->latest()->get(),
            'members' => Member::query()->active()->orderBy('name')->get(),
        ]);
    }

    public function store(ShoppingItemRequest $request, ActivityLogService $activityLogService)
    {
        $item = ShoppingItem::query()->create($request->validated());
        $activityLogService->log('shopping.item', "Added shopping item {$item->name}.", $item);

        return back()->with('status', 'Shopping item added.');
    }

    public function update(ShoppingItemRequest $request, ShoppingItem $shoppingItem, ActivityLogService $activityLogService)
    {
        $shoppingItem->update($request->validated());
        $activityLogService->log('shopping.updated', "Updated shopping item {$shoppingItem->name}.", $shoppingItem);

        return back()->with('status', 'Shopping item updated.');
    }

    public function togglePurchased(ShoppingItem $shoppingItem, ActivityLogService $activityLogService)
    {
        $shoppingItem->update(['is_purchased' => ! $shoppingItem->is_purchased]);
        $activityLogService->log(
            'shopping.toggled',
            $shoppingItem->is_purchased ? "Marked {$shoppingItem->name} purchased." : "Marked {$shoppingItem->name} pending.",
            $shoppingItem,
        );

        return back()->with('status', $shoppingItem->is_purchased ? 'Shopping item marked purchased.' : 'Shopping item marked pending.');
    }

    public function destroy(ShoppingItem $shoppingItem, ActivityLogService $activityLogService)
    {
        $shoppingItem->delete();
        $activityLogService->log('shopping.deleted', "Deleted shopping item {$shoppingItem->name}.", $shoppingItem);

        return back()->with('status', 'Shopping item deleted.');
    }
}
