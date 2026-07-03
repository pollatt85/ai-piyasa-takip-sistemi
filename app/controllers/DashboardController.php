<?php
declare(strict_types=1);

class DashboardController extends Controller
{
    public function index(): void
    {
        $signal = new Signal();
        $project = new Project();
        $task = new Task();
        $tick = new Tick();

        $radarRange = ($_GET['range'] ?? '7g') === '24s' ? '24s' : '7g';
        $since = date('Y-m-d H:i:s', strtotime($radarRange === '24s' ? '-24 hours' : '-7 days'));

        $this->view('dashboard/index', [
            'title' => 'Dashboard',
            // Üst özet kartları
            'todaySignals' => $signal->todayCount(),
            'weekMature' => $signal->weekMatureCount(),
            'activeProjects' => $project->activeCount(),
            'weeklyCompletion' => $task->weeklyCompletion(),
            // Fırsat Radarı
            'radarRange' => $radarRange,
            'newSignals' => $signal->filtered(['since' => $since, 'limit' => 8]),
            'matureSignals' => $signal->filtered(['status' => 'olgun_firsat', 'limit' => 8]),
            'favorites' => $signal->filtered(['favorites' => true, 'limit' => 8]),
            // Piyasa haritası + grafikler
            'tree' => (new SubSector())->tree(),
            'sectorDistribution' => $signal->sectorDistribution(),
            'weeklyTicks' => $tick->weeklySeries(),
            // Kanban + görevler + aktivite
            'kanban' => $project->kanban(),
            'openTasks' => $task->openTasks(8),
            'activities' => (new Log())->latest(12),
            'sectors' => (new Sector())->allWithCategory(),
        ]);
    }
}
