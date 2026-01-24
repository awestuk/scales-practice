<?php
namespace App\Models;

use App\Storage\Db;
use PDO;

class Scale
{
    public int $id;
    public string $name;
    public ?string $notes;
    public ?string $type;

    public static function findAll(): array
    {
        $stmt = Db::getInstance()->query('
            SELECT * FROM scales ORDER BY name
        ');
        return $stmt->fetchAll(PDO::FETCH_CLASS, self::class);
    }

    public static function findByType(?string $type): array
    {
        if ($type === null || $type === '') {
            return self::findAll();
        }

        $stmt = Db::getInstance()->prepare('
            SELECT * FROM scales WHERE type = ? ORDER BY name
        ');
        $stmt->execute([$type]);
        return $stmt->fetchAll(PDO::FETCH_CLASS, self::class);
    }

    public static function getTypes(): array
    {
        return ScaleType::getNames();
    }
    
    public static function find(int $id): ?self
    {
        $stmt = Db::getInstance()->prepare('
            SELECT * FROM scales WHERE id = ?
        ');
        $stmt->execute([$id]);
        $stmt->setFetchMode(PDO::FETCH_CLASS, self::class);
        return $stmt->fetch() ?: null;
    }
    
    public static function create(string $name, ?string $notes = null, ?string $type = 'Other'): self
    {
        $stmt = Db::getInstance()->prepare('
            INSERT INTO scales (name, notes, type) VALUES (?, ?, ?)
        ');
        $stmt->execute([$name, $notes, $type ?? 'Other']);

        $scale = new self();
        $scale->id = (int)Db::getInstance()->lastInsertId();
        $scale->name = $name;
        $scale->notes = $notes;
        $scale->type = $type ?? 'Other';

        return $scale;
    }

    public function update(string $name, ?string $notes = null, ?string $type = null): void
    {
        $stmt = Db::getInstance()->prepare('
            UPDATE scales SET name = ?, notes = ?, type = ? WHERE id = ?
        ');
        $stmt->execute([$name, $notes, $type ?? $this->type, $this->id]);

        $this->name = $name;
        $this->notes = $notes;
        if ($type !== null) {
            $this->type = $type;
        }
    }
    
    public function delete(): void
    {
        $stmt = Db::getInstance()->prepare('
            DELETE FROM scales WHERE id = ?
        ');
        $stmt->execute([$this->id]);
    }
}