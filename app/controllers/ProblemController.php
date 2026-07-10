<?php
declare(strict_types=1);

/**
 * Fırsat (problem) yönetimi — eski SignalController'ın yerini alır. Liste fırsat
 * kartları, detay 12 boyut + kanıtlar. Tarama verisi salt-okunur üretir; buradaki
 * aksiyonlar favori/manuel puan/projeye çevir/sil ve manuel ekleme.
 */
class ProblemController extends Controller
{
    public function index(): void
    {
        $filters = [
            'status' => $_GET['status'] ?? '',
            'region' => $_GET['region'] ?? '',
            'sector_id' => (int) ($_GET['sector_id'] ?? 0),
            'favorites' => isset($_GET['favorites']),
        ];
        $this->view('problems/index', [
            'title' => 'Fırsatlar',
            'problems' => (new Problem())->filtered($filters),
            'filters' => $filters,
            'sectors' => (new Sector())->allWithCategory(),
            'subSectors' => (new SubSector())->all('name'),
        ]);
    }

    public function show(int $id): void
    {
        $problem = (new Problem())->findWithSector($id);
        if ($problem === null) {
            flash('Fırsat bulunamadı.', 'error');
            $this->redirect('problems');
        }
        $this->view('problems/show', [
            'title' => mb_substr((string) $problem['title'], 0, 60),
            'problem' => $problem,
            'mentions' => (new ProblemMention())->forProblem($id),
        ]);
    }

    /** Manuel problem/fırsat ekleme (dashboard hızlı aksiyonu). */
    public function store(): void
    {
        $content = (string) $this->input('content', '');
        if ($content === '') {
            flash('İçerik boş olamaz.', 'error');
            $this->redirect('problems');
        }
        $subSectorId = (int) $this->input('sub_sector_id', '0');
        if ($subSectorId === 0) {
            $subSectorId = (new SubSector())->fallbackId();
        }
        $region = $this->input('region', 'TR') === 'Global' ? 'Global' : 'TR';
        (new ProblemCluster())->assign($content, null, $subSectorId, $region, 'manuel', (string) $this->input('source_url', ''));
        flash('Problem eklendi.');
        $this->redirect('problems');
    }

    public function favorite(int $id): void
    {
        $problem = new Problem();
        $row = $problem->find($id);
        if ($row !== null) {
            $problem->update($id, ['is_favorite' => $row['is_favorite'] ? 0 : 1]);
        }
        $this->redirect($_POST['back'] ?? 'problems');
    }

    /** Manuel puan (override) — skor yeniden hesaplanır. */
    public function score(int $id): void
    {
        $problem = new Problem();
        if ($problem->find($id) !== null) {
            $problem->update($id, ['manual_score' => max(0, (int) $this->input('manual_score', '0'))]);
            $problem->recompute($id);
            flash('Manuel puan güncellendi.');
        }
        $this->redirect($_POST['back'] ?? 'problems');
    }

    /** "Projeye Çevir" — başlık/sektör/kaynak otomatik taslak olarak aktarılır. */
    public function toProject(int $id): void
    {
        $row = (new Problem())->find($id);
        if ($row === null) {
            $this->redirect('problems');
        }
        $projectId = (new Project())->createFromProblem($row);
        flash('Fırsat projeye çevrildi.');
        $this->redirect('projects/' . $projectId);
    }

    public function destroy(int $id): void
    {
        (new Problem())->delete($id); // problem_mentions CASCADE ile silinir
        flash('Fırsat silindi.');
        $this->redirect('problems');
    }

    /** TR çeviri butonu (AJAX) — MyMemory API, ücretsiz/key'siz. */
    public function translate(int $id): void
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode((new Problem())->translate($id), JSON_UNESCAPED_UNICODE);
    }
}
