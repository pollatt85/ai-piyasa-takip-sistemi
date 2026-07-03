<?php
declare(strict_types=1);

class SignalController extends Controller
{
    public function index(): void
    {
        $filters = [
            'status' => $_GET['status'] ?? '',
            'region' => $_GET['region'] ?? '',
            'sector_id' => (int) ($_GET['sector_id'] ?? 0),
            'favorites' => isset($_GET['favorites']),
        ];
        $this->view('signals/index', [
            'title' => 'Sinyaller & Fırsatlar',
            'signals' => (new Signal())->filtered($filters),
            'filters' => $filters,
            'sectors' => (new Sector())->allWithCategory(),
            'subSectors' => (new SubSector())->all('name'),
        ]);
    }

    /** Manuel sinyal/fırsat ekleme (dashboard hızlı aksiyonu). */
    public function store(): void
    {
        $content = (string) $this->input('content', '');
        if ($content === '') {
            flash('İçerik boş olamaz.', 'error');
            $this->redirect('signals');
        }
        (new Signal())->upsertFromScan(
            (int) $this->input('sub_sector_id', '0'),
            'manuel',
            (string) $this->input('source_url', ''),
            $content,
            $this->input('region', 'TR') === 'Global' ? 'Global' : 'TR'
        );
        flash('Sinyal eklendi.');
        $this->redirect('signals');
    }

    public function favorite(int $id): void
    {
        $signal = new Signal();
        $row = $signal->find($id);
        if ($row !== null) {
            $signal->update($id, ['is_favorite' => $row['is_favorite'] ? 0 : 1]);
        }
        $this->redirect($_POST['back'] ?? 'signals');
    }

    /** Manuel puan (override) — skor yeniden hesaplanır. */
    public function score(int $id): void
    {
        $signal = new Signal();
        if ($signal->find($id) !== null) {
            $signal->update($id, ['manual_score' => max(0, (int) $this->input('manual_score', '0'))]);
            $signal->recompute($id);
            flash('Manuel puan güncellendi.');
        }
        $this->redirect($_POST['back'] ?? 'signals');
    }

    /** "Projeye Çevir" — başlık/sektör/kaynak otomatik taslak olarak aktarılır. */
    public function toProject(int $id): void
    {
        $row = (new Signal())->find($id);
        if ($row === null) {
            $this->redirect('signals');
        }
        $projectId = (new Project())->createFromSignal($row);
        flash('Fırsat projeye çevrildi.');
        $this->redirect('projects/' . $projectId);
    }

    public function destroy(int $id): void
    {
        (new Signal())->delete($id);
        flash('Sinyal silindi.');
        $this->redirect('signals');
    }

    /** TR çeviri butonu (AJAX) — MyMemory API, ücretsiz/key'siz. */
    public function translate(int $id): void
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode((new Signal())->translate($id), JSON_UNESCAPED_UNICODE);
    }
}
