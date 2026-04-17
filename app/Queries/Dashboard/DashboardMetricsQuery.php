<?php

namespace App\Queries\Dashboard;

use App\Models\Sample;
use App\Models\Client;

class DashboardMetricsQuery
{
    public function get(): array
    {
        return [
            'totalActive'    => Sample::active()->count(),
            'totalCollected' => Sample::active()->byStatus('collected')->count(),
            'totalAccepted'  => Sample::active()->byStatus('accepted')->count(),
            'totalCompleted' => Sample::active()->byStatus('completed')->count(),
            'totalClients'   => Client::active()->count(),
        ];
    }
}
