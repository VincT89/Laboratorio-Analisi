<?php

namespace App\Queries\Dashboard;

use App\Models\Sample;

class DashboardCalendarQuery
{
    public function get(): array
    {
        return Sample::active()
            ->whereMonth('collected_at', now()->month)
            ->whereYear('collected_at', now()->year)
            ->get()
            ->groupBy(fn($s) => $s->collected_at->day)
            ->map(fn($group) => $group->count())
            ->toArray();
    }
}
