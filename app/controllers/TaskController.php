<?php
declare(strict_types=1);

class TaskController extends Controller
{
    public function store(int $projectId): void
    {
        $title = (string) $this->input('title', '');
        if ($title === '') {
            flash('Görev başlığı gerekli.', 'error');
            $this->redirect('projects/' . $projectId);
        }
        (new Task())->insert([
            'project_id' => $projectId,
            'phase' => (string) $this->input('phase', 'Genel'),
            'title' => $title,
            'description' => (string) $this->input('description', ''),
            'status' => 'acik',
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        flash('Görev eklendi.');
        $this->redirect('projects/' . $projectId);
    }

    /** Hızlı tikleme: tamamla → tick kaydı + log; geri al → tick sil. */
    public function toggle(int $id): void
    {
        $task = new Task();
        $row = $task->find($id);
        if ($row === null) {
            $this->redirect('projects');
        }
        $tick = new Tick();
        if ($row['status'] === 'acik') {
            $task->update($id, ['status' => 'tamamlandi']);
            $tick->insert(['task_id' => $id, 'created_at' => date('Y-m-d H:i:s')]);
            (new Log())->add('gorev_tamamlandi', 'Görev tamamlandı: ' . $row['title'], $id);
        } else {
            $task->update($id, ['status' => 'acik']);
            Database::pdo()->prepare('DELETE FROM ticks WHERE task_id = ?')->execute([$id]);
        }
        $this->redirect($_POST['back'] ?? 'projects/' . $row['project_id']);
    }

    public function destroy(int $id): void
    {
        $task = new Task();
        $row = $task->find($id);
        if ($row !== null) {
            $task->delete($id);
            flash('Görev silindi.');
            $this->redirect('projects/' . $row['project_id']);
        }
        $this->redirect('projects');
    }
}
