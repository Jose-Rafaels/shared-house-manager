<?php

namespace App\Services;

class MoneySplitService
{
    public function splitEvenly(int $totalAmount, int $participants): array
    {
        if ($participants < 1) {
            return [];
        }

        $baseShare = intdiv($totalAmount, $participants);
        $remainder = $totalAmount % $participants;

        return collect(range(1, $participants))
            ->map(fn (int $index) => $baseShare + ($index <= $remainder ? 1 : 0))
            ->all();
    }
}
