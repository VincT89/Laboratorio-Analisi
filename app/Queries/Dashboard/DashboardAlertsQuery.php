<?php

namespace App\Queries\Dashboard;

use App\Models\Sample;
use App\Models\Client;
use App\Models\SampleFile;

class DashboardAlertsQuery
{
    public function get(): array
    {
        return [
            'overdueCollected' => Sample::active()
                ->byStatus('collected')
                ->where('collected_at', '<=', now()->subHours(48))
                ->count(),

            'samplesWithoutFiles' => Sample::active()
                ->byStatus('completed')
                ->whereDoesntHave('files', fn($q) => $q->active())
                ->count(),

            'newClientsThisMonth' => Client::active()
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),

            'filesUploadedToday' => SampleFile::active()
                ->whereDate('created_at', today())
                ->count(),
        ];
    }
}
