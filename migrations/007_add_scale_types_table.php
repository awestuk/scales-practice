<?php
namespace Migrations;

use App\Storage\Migration;

class Migration007AddScaleTypesTable extends Migration
{
    public function getVersion(): string
    {
        return '007';
    }

    public function getName(): string
    {
        return 'Add scale_types table for dynamic type management';
    }

    public function up(): void
    {
        // Create scale_types table
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS scale_types (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL UNIQUE
            )
        ");

        // Populate with existing distinct types from scales
        $this->db->exec("
            INSERT OR IGNORE INTO scale_types (name)
            SELECT DISTINCT type FROM scales WHERE type IS NOT NULL AND type != ''
        ");

        // Ensure 'Other' exists as a fallback
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM scale_types WHERE name = ?');
        $stmt->execute(['Other']);
        if ($stmt->fetchColumn() == 0) {
            $stmt = $this->db->prepare('INSERT INTO scale_types (name) VALUES (?)');
            $stmt->execute(['Other']);
        }
    }
}
