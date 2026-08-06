<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Services\DashboardService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request, DashboardService $dashboardService)
    {
        $rawMonth = $request->input('month');

        if ($rawMonth === 'all') {
            $selectedMonth = 'all';
            $serviceMonth = null;
        } elseif ($rawMonth !== null && preg_match('/^\d{4}-\d{2}$/', $rawMonth)) {
            $selectedMonth = $rawMonth;
            $serviceMonth = $rawMonth;
        } else {
            // absent or invalid => current month
            $selectedMonth = now()->format('Y-m');
            $serviceMonth = $selectedMonth;
        }

        $categoryId = $request->input('category_id');
        $categoryId = $categoryId ? (int) $categoryId : null;

        return view('dashboard', $dashboardService->summary($serviceMonth, $categoryId))
            ->with('selectedMonth', $selectedMonth)
            ->with('selectedCategoryId', $categoryId)
            ->with('categories', Category::query()->orderBy('name')->get())
            ->with('months', $dashboardService->availableMonths());
    }
}