<?php

namespace App\Queries\Dashboard;

use App\Models\Sample;

class DashboardChartQuery
{
    /**
     * @todo N+1 mensile: sostituire questo loop con groupby/selectRaw
     * dopo aver verificato compatibilità con driver db misti (mysql/sqlite).
     */
    public function get(): array
    {
        $chartLabels = [];
        $chartCreated = [];
        $chartCompleted = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $chartLabels[] = ucfirst($month->translatedFormat('M'));
            
            $chartCreated[] = Sample::whereYear('created_at', $month->year)
                                    ->whereMonth('created_at', $month->month)
                                    ->count();
                                    
            $chartCompleted[] = Sample::whereYear('updated_at', $month->year)
                                      ->whereMonth('updated_at', $month->month)
                                      ->where('status', 'completed')
                                      ->count();
        }

        return [
            'chartLabels' => $chartLabels,
            'chartCreated' => $chartCreated,
            'chartCompleted' => $chartCompleted
        ];
    }
}
