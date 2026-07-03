<?php
declare(strict_types=1);

class Company extends Model
{
    protected string $table = 'companies';

    public function allWithHierarchy(): array
    {
        return $this->rows(
            'SELECT co.*, ss.name AS sub_sector_name, s.name AS sector_name
             FROM companies co
             JOIN sub_sectors ss ON ss.id = co.sub_sector_id
             JOIN sectors s ON s.id = ss.sector_id
             ORDER BY co.created_at DESC'
        );
    }
}
