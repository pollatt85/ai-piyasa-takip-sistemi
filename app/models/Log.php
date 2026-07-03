<?php
declare(strict_types=1);

class Log extends Model
{
    protected string $table = 'logs';

    public function add(string $type, string $message, ?int $refId = null): void
    {
        $this->insert([
            'type' => $type,
            'message' => $message,
            'ref_id' => $refId,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function latest(int $limit = 15): array
    {
        return $this->rows('SELECT * FROM logs ORDER BY id DESC LIMIT ' . (int) $limit);
    }
}
