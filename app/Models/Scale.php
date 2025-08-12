<?php
namespace App\Models;

use App\Storage\Db;
use PDO;

class Scale
{
    public int $id;
    public string $name;
    public ?string $notes;
    
    public static function findAll(): array
    {
        $stmt = Db::getInstance()->query('
            SELECT * FROM scales ORDER BY name
        ');
        return $stmt->fetchAll(PDO::FETCH_CLASS, self::class);
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
    
    public static function create(string $name, ?string $notes = null): self
    {
        $stmt = Db::getInstance()->prepare('
            INSERT INTO scales (name, notes) VALUES (?, ?)
        ');
        $stmt->execute([$name, $notes]);
        
        $scale = new self();
        $scale->id = (int)Db::getInstance()->lastInsertId();
        $scale->name = $name;
        $scale->notes = $notes;
        
        return $scale;
    }
    
    public function update(string $name, ?string $notes = null): void
    {
        $stmt = Db::getInstance()->prepare('
            UPDATE scales SET name = ?, notes = ? WHERE id = ?
        ');
        $stmt->execute([$name, $notes, $this->id]);
        
        $this->name = $name;
        $this->notes = $notes;
    }
    
    public function delete(): void
    {
        $stmt = Db::getInstance()->prepare('
            DELETE FROM scales WHERE id = ?
        ');
        $stmt->execute([$this->id]);
    }
}