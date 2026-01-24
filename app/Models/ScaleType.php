<?php
namespace App\Models;

use App\Storage\Db;
use PDO;

class ScaleType
{
    public int $id;
    public string $name;

    public static function findAll(): array
    {
        $stmt = Db::getInstance()->query('
            SELECT * FROM scale_types ORDER BY name
        ');
        return $stmt->fetchAll(PDO::FETCH_CLASS, self::class);
    }

    public static function find(int $id): ?self
    {
        $stmt = Db::getInstance()->prepare('
            SELECT * FROM scale_types WHERE id = ?
        ');
        $stmt->execute([$id]);
        $stmt->setFetchMode(PDO::FETCH_CLASS, self::class);
        return $stmt->fetch() ?: null;
    }

    public static function findByName(string $name): ?self
    {
        $stmt = Db::getInstance()->prepare('
            SELECT * FROM scale_types WHERE name = ?
        ');
        $stmt->execute([$name]);
        $stmt->setFetchMode(PDO::FETCH_CLASS, self::class);
        return $stmt->fetch() ?: null;
    }

    public static function create(string $name): self
    {
        $stmt = Db::getInstance()->prepare('
            INSERT INTO scale_types (name) VALUES (?)
        ');
        $stmt->execute([$name]);

        $type = new self();
        $type->id = (int)Db::getInstance()->lastInsertId();
        $type->name = $name;

        return $type;
    }

    public function delete(): bool
    {
        // Don't delete if scales are using this type
        $stmt = Db::getInstance()->prepare('
            SELECT COUNT(*) FROM scales WHERE type = ?
        ');
        $stmt->execute([$this->name]);
        if ($stmt->fetchColumn() > 0) {
            return false;
        }

        $stmt = Db::getInstance()->prepare('
            DELETE FROM scale_types WHERE id = ?
        ');
        $stmt->execute([$this->id]);
        return true;
    }

    public static function getNames(): array
    {
        $stmt = Db::getInstance()->query('
            SELECT name FROM scale_types ORDER BY name
        ');
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
