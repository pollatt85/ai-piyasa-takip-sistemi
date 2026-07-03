<?php
declare(strict_types=1);

abstract class Model
{
    protected string $table;

    protected function db(): PDO
    {
        return Database::pdo();
    }

    public function all(string $orderBy = 'id'): array
    {
        return $this->db()->query("SELECT * FROM {$this->table} ORDER BY {$orderBy}")->fetchAll();
    }

    public function find(int $id): ?array
    {
        return $this->row("SELECT * FROM {$this->table} WHERE id = ?", [$id]);
    }

    public function insert(array $data): int
    {
        $cols = array_keys($data);
        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $this->table,
            implode(', ', $cols),
            implode(', ', array_fill(0, count($cols), '?'))
        );
        $this->db()->prepare($sql)->execute(array_values($data));
        return (int) $this->db()->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $set = implode(', ', array_map(fn(string $c): string => "{$c} = ?", array_keys($data)));
        $st = $this->db()->prepare("UPDATE {$this->table} SET {$set} WHERE id = ?");
        $st->execute([...array_values($data), $id]);
    }

    public function delete(int $id): void
    {
        $this->db()->prepare("DELETE FROM {$this->table} WHERE id = ?")->execute([$id]);
    }

    protected function rows(string $sql, array $params = []): array
    {
        $st = $this->db()->prepare($sql);
        $st->execute($params);
        return $st->fetchAll();
    }

    protected function row(string $sql, array $params = []): ?array
    {
        $st = $this->db()->prepare($sql);
        $st->execute($params);
        $row = $st->fetch();
        return $row === false ? null : $row;
    }

    protected function value(string $sql, array $params = []): mixed
    {
        $st = $this->db()->prepare($sql);
        $st->execute($params);
        $v = $st->fetchColumn();
        return $v === false ? null : $v;
    }
}
