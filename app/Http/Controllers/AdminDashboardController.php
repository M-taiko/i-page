<?php

namespace App\Http\Controllers;

use App\Services\DashboardStatsService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function __invoke(Request $request, DashboardStatsService $stats): View
    {
        abort_unless($request->user()->hasRole('super_admin'), 403);

        $days = DashboardStatsService::normalizeRange($request->query('range'));

        return view('admin.dashboard', ['stats' => $stats->platform($days), 'days' => $days]);
    }
}
