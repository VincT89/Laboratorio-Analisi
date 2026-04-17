<?php

namespace App\Queries\Dashboard;

use App\Models\Sample;
use Illuminate\Database\Eloquent\Collection;

class SensitiveIncompleteSamplesQuery
{
    public function get(int $limit = 8): Collection
    {
        return Sample::active()
            ->sensitiveIncomplete()
            ->with('createdBy')
            ->oldest('collected_at')
            ->limit($limit)
            ->get();
    }

    public function count(): int
    {
        return Sample::active()->sensitiveIncomplete()->count();
    }
}
