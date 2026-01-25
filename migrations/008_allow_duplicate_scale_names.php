<?php
namespace Migrations;

use App\Storage\Migration;

class Migration008AllowDuplicateScaleNames extends Migration
{
    public function getVersion(): string
    {
        return '008';
    }

    public function getName(): string
    {
        return 'Allow duplicate scale names with different types';
    }

    public function up(): void
    {
        // SQLite doesn't support DROP CONSTRAINT, so we need to recreate the table
        // Create new table without the unique constraint on name alone
        $this->db->exec("
            CREATE TABLE scales_new (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                notes TEXT,
                type TEXT DEFAULT 'Other',
                UNIQUE(name, type)
            )
        ");

        // Copy data
        $this->db->exec("
            INSERT INTO scales_new (id, name, notes, type)
            SELECT id, name, notes, type FROM scales
        ");

        // Drop old table and rename new one
        $this->db->exec("DROP TABLE scales");
        $this->db->exec("ALTER TABLE scales_new RENAME TO scales");
    }
}
