<?php

namespace App\Http\Controllers;

use App\Models\Sample;
use App\Models\Client;
use App\Models\SampleFile;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Carbon;

use App\Support\Dashboard\AdminDashboardDataBuilder;
use App\Support\Dashboard\StaffDashboardDataBuilder;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __construct(
        private AdminDashboardDataBuilder $adminBuilder,
        private StaffDashboardDataBuilder $staffBuilder
    ) {}

    public function index()
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            $data = $this->adminBuilder->buildFor();
        } else {
            $data = $this->staffBuilder->buildFor();
        }

        return view('dashboard', $data);
    }
}