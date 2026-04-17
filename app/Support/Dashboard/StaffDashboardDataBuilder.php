<?php

namespace App\Support\Dashboard;

use App\Models\User;
use App\Models\Sample;
use App\Queries\Dashboard\DashboardMetricsQuery;
use App\Queries\Dashboard\RecentSamplesQuery;
use App\Queries\Dashboard\DashboardAlertsQuery;
use App\Queries\Dashboard\DashboardChartQuery;
use App\Queries\Dashboard\DashboardCalendarQuery;
use App\Queries\Dashboard\UpcomingSamplesQuery;

class StaffDashboardDataBuilder
{
    public function __construct(
        private DashboardMetricsQuery $metricsQuery,
        private RecentSamplesQuery $recentSamplesQuery,
        private DashboardAlertsQuery $alertsQuery,
        private DashboardChartQuery $chartQuery,
        private DashboardCalendarQuery $calendarQuery,
        private UpcomingSamplesQuery $upcomingQuery
    ) {}

    public function buildFor(): array
    {
        $data = array_merge(
            $this->metricsQuery->get(),
            $this->alertsQuery->get()
        );

        $data['recentSamples'] = $this->recentSamplesQuery->get()
            ->map(fn($s) => new \App\ViewModels\SampleRowViewModel($s, false));
        // Niente recent activities, niente logiche di queue sensibile

        $data = array_merge($data, $this->chartQuery->get());
        
        $data['calendarData']    = $this->calendarQuery->get();
        $data['upcomingSamples'] = $this->upcomingQuery->get()
            ->map(fn($s) => new \App\ViewModels\SampleRowViewModel($s, false));

        return $data;
    }
}
