<?php
declare(strict_types=1);

class ProjectController extends Controller
{
    public function index(): void
    {
        $this->view('projects/index', [
            'title' => 'Projeler',
            'kanban' => (new Project())->kanban(),
            'sectors' => (new Sector())->allWithCategory(),
        ]);
    }

    public function show(int $id): void
    {
        $project = (new Project())->findWithSector($id);
        if ($project === null) {
            flash('Proje bulunamadı.', 'error');
            $this->redirect('projects');
        }
        $tasks = (new Task())->forProject($id);
        $done = count(array_filter($tasks, fn(array $t): bool => $t['status'] === 'tamamlandi'));
        $this->view('projects/show', [
            'title' => $project['title'],
            'project' => $project,
            'tasks' => $tasks,
            'progress' => count($tasks) > 0 ? (int) round($done / count($tasks) * 100) : 0,
        ]);
    }

    /** Manuel proje açma (fırsattan bağımsız). */
    public function store(): void
    {
        $title = (string) $this->input('title', '');
        if ($title === '') {
            flash('Proje başlığı gerekli.', 'error');
            $this->redirect('projects');
        }
        $id = (new Project())->insert([
            'title' => $title,
            'sector_id' => (int) $this->input('sector_id', '0') ?: null,
            'status' => 'yeni',
            'current_phase' => (string) $this->input('current_phase', 'Planlama'),
            'region' => $this->input('region', 'TR') === 'Global' ? 'Global' : 'TR',
            'source_links' => (string) $this->input('source_links', ''),
            'notes' => '',
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        (new Log())->add('proje_acildi', 'Yeni proje: ' . $title, $id);
        flash('Proje oluşturuldu.');
        $this->redirect('projects/' . $id);
    }

    public function updateStatus(int $id): void
    {
        $status = (string) $this->input('status', '');
        $project = new Project();
        $row = $project->find($id);
        if ($row !== null && array_key_exists($status, Project::STATUSES)) {
            $project->update($id, ['status' => $status]);
            (new Log())->add('proje_durum', sprintf('"%s" durumu: %s', $row['title'], Project::STATUSES[$status]), $id);
            flash('Durum güncellendi.');
        }
        $this->redirect($_POST['back'] ?? 'projects');
    }

    public function updateDetails(int $id): void
    {
        $project = new Project();
        if ($project->find($id) !== null) {
            $project->update($id, [
                'current_phase' => (string) $this->input('current_phase', ''),
                'notes' => (string) $this->input('notes', ''),
            ]);
            flash('Proje güncellendi.');
        }
        $this->redirect('projects/' . $id);
    }

    public function destroy(int $id): void
    {
        (new Project())->delete($id);
        flash('Proje silindi.');
        $this->redirect('projects');
    }
}
