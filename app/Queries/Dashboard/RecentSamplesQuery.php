<?php

namespace App\Queries\Dashboard;

use App\Models\Sample;
use Illuminate\Database\Eloquent\Collection;

class RecentSamplesQuery
{
    public function get(int $limit = 8): Collection
    {
        return Sample::active()
            ->with('client')
            ->orderByRaw("CASE status WHEN 'collected' THEN 0 WHEN 'accepted' THEN 1 ELSE 2 END")
            ->orderByDesc('collected_at')
            ->limit($limit)
            ->get();
    }
}
