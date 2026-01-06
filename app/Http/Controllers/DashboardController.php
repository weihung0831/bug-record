<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Bug;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        $statusCounts = Bug::query()
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $priorityCounts = Bug::query()
            ->selectRaw('priority, count(*) as count')
            ->groupBy('priority')
            ->pluck('count', 'priority');

        $dailyBugs = Bug::query()
            ->selectRaw('DATE(created_at) as date, count(*) as count')
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $assigneeCounts = Bug::query()
            ->selectRaw('assignee_id, count(*) as count')
            ->whereNotNull('assignee_id')
            ->whereNot('status', 'closed')
            ->groupBy('assignee_id')
            ->with('assignee:id,name')
            ->get();

        $recentBugs = Bug::query()
            ->with(['reporter:id,name', 'assignee:id,name', 'tags:id,name,color'])
            ->latest()
            ->take(5)
            ->get();

        $recentActivities = Activity::query()
            ->with(['bug:id,title', 'user:id,name'])
            ->latest()
            ->take(10)
            ->get();

        return Inertia::render('Dashboard', [
            'stats' => [
                'total' => Bug::count(),
                'open' => $statusCounts['open'] ?? 0,
                'inProgress' => $statusCounts['in_progress'] ?? 0,
                'resolved' => $statusCounts['resolved'] ?? 0,
                'closed' => $statusCounts['closed'] ?? 0,
            ],
            'statusCounts' => $statusCounts,
            'priorityCounts' => $priorityCounts,
            'dailyBugs' => $dailyBugs,
            'assigneeCounts' => $assigneeCounts,
            'recentBugs' => $recentBugs,
            'recentActivities' => $recentActivities,
        ]);
    }
}
