<?php
declare(strict_types=1);

class Sector extends Model
{
    protected string $table = 'sectors';

    public function allWithCategory(): array
    {
        return $this->rows(
            'SELECT s.*, c.name AS category_name
             FROM sectors s JOIN categories c ON c.id = s.category_id
             ORDER BY c.name, s.name'
        );
    }
}
