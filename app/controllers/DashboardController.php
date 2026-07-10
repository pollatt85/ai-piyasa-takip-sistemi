<?php
declare(strict_types=1);

class DashboardController extends Controller
{
    public function index(): void
    {
        $problem = new Problem();
        $project = new Project();
        $task = new Task();
        $tick = new Tick();

        $radarRange = ($_GET['range'] ?? '7g') === '24s' ? '24s' : '7g';
        $since = date('Y-m-d H:i:s', strtotime($radarRange === '24s' ? '-24 hours' : '-7 days'));

        $this->view('dashboard/index', [
            'title' => 'Dashboard',
            // Üst özet kartları
            'todaySignals' => $problem->todayCount(),
            'weekMature' => $problem->weekMatureCount(),
            'activeProjects' => $project->activeCount(),
            'weeklyCompletion' => $task->weeklyCompletion(),
            // Fırsat Radarı
            'radarRange' => $radarRange,
            'newSignals' => $problem->filtered(['since' => $since, 'limit' => 8]),
            'matureSignals' => $problem->filtered(['status' => 'olgun_firsat', 'limit' => 8]),
            'favorites' => $problem->filtered(['favorites' => true, 'limit' => 8]),
            // Piyasa haritası + grafikler
            'tree' => (new SubSector())->tree(),
            'sectorDistribution' => $problem->sectorDistribution(),
            'weeklyTicks' => $tick->weeklySeries(),
            // Kanban + görevler + aktivite
            'kanban' => $project->kanban(),
            'openTasks' => $task->openTasks(8),
            'activities' => (new Log())->latest(12),
            'sectors' => (new Sector())->allWithCategory(),
        ]);
    }
}
