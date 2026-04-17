<?php

namespace App\Queries\Dashboard;

use App\Models\Sample;
use Illuminate\Database\Eloquent\Collection;

class UpcomingSamplesQuery
{
    public function get(int $limit = 5): Collection
    {
        return Sample::active()
            ->with('client')
            ->whereMonth('collected_at', now()->month)
            ->whereYear('collected_at', now()->year)
            ->orderByDesc('collected_at')
            ->limit($limit)
            ->get();
    }
}
