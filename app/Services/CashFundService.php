<?php

namespace App\Services;

use App\Models\CashFundEntry;

class CashFundService
{
    public function balance(): int
    {
        $contributions = CashFundEntry::query()
            ->where('type', 'contribution')
            ->sum('amount');

        $expenses = CashFundEntry::query()
            ->where('type', 'expense')
            ->sum('amount');

        return $contributions - $expenses;
    }
}
