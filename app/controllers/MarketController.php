<?php
declare(strict_types=1);

class MarketController extends Controller
{
    public function index(): void
    {
        $this->view('market/index', [
            'title' => 'Piyasa Haritası',
            'tree' => (new SubSector())->tree(),
            'categories' => (new Category())->all('name'),
            'sectors' => (new Sector())->allWithCategory(),
            'subSectors' => (new SubSector())->all('name'),
            'companies' => (new Company())->allWithHierarchy(),
        ]);
    }

    public function storeCategory(): void
    {
        $name = (string) $this->input('name', '');
        if ($name !== '') {
            (new Category())->insert(['name' => $name, 'created_at' => date('Y-m-d H:i:s')]);
            flash('Kategori eklendi.');
        }
        $this->redirect('market');
    }

    public function storeSector(): void
    {
        $name = (string) $this->input('name', '');
        $categoryId = (int) $this->input('category_id', '0');
        if ($name !== '' && $categoryId > 0) {
            (new Sector())->insert(['category_id' => $categoryId, 'name' => $name, 'created_at' => date('Y-m-d H:i:s')]);
            flash('Sektör eklendi.');
        }
        $this->redirect('market');
    }

    public function storeSubSector(): void
    {
        $name = (string) $this->input('name', '');
        $sectorId = (int) $this->input('sector_id', '0');
        if ($name !== '' && $sectorId > 0) {
            (new SubSector())->insert([
                'sector_id' => $sectorId,
                'name' => $name,
                'keywords' => (string) $this->input('keywords', ''),
                'is_fallback' => 0,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
            flash('Alt sektör eklendi.');
        }
        $this->redirect('market');
    }

    public function storeCompany(): void
    {
        $name = (string) $this->input('name', '');
        $subSectorId = (int) $this->input('sub_sector_id', '0');
        if ($name !== '' && $subSectorId > 0) {
            (new Company())->insert([
                'sub_sector_id' => $subSectorId,
                'name' => $name,
                'note' => (string) $this->input('note', ''),
                'created_at' => date('Y-m-d H:i:s'),
            ]);
            flash('Şirket eklendi.');
        }
        $this->redirect('market');
    }

    public function destroyCompany(int $id): void
    {
        (new Company())->delete($id);
        flash('Şirket silindi.');
        $this->redirect('market');
    }
}
