<?php

namespace App\Queries\Samples;

use App\Models\Sample;

class SampleMetricsQuery
{
    public function get(): array
    {
        return [
            'totalActive'    => Sample::active()->count(),
            'totalCollected' => Sample::active()->byStatus('collected')->count(),
            'totalAccepted'  => Sample::active()->byStatus('accepted')->count(),
            'totalCompleted' => Sample::active()->byStatus('completed')->count(),
        ];
    }
}
