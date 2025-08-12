<?php
namespace Migrations;

use App\Storage\Migration;

class Migration002AddBrowserSessions extends Migration
{
    public function getVersion(): string
    {
        return '002';
    }
    
    public function getName(): string
    {
        return 'Add browser session support for multi-user isolation';
    }
    
    public function up(): void
    {
        // Check if browser_session_id column already exists
        if (!$this->columnExists('sessions', 'browser_session_id')) {
            // Create new table with updated schema
            $this->db->exec('
                CREATE TABLE IF NOT EXISTS sessions_new (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    browser_session_id TEXT NOT NULL DEFAULT "default",
                    session_date TEXT NOT NULL,
                    started_at TEXT NOT NULL,
                    ended_at TEXT,
                    required_successes INTEGER NOT NULL,
                    status TEXT CHECK(status IN ("active", "completed"))
                )
            ');
            
            // Copy existing data if table exists
            if ($this->tableExists('sessions')) {
                $this->db->exec('
                    INSERT INTO sessions_new (id, browser_session_id, session_date, started_at, ended_at, required_successes, status)
                    SELECT id, "default", session_date, started_at, ended_at, required_successes, status
                    FROM sessions
                ');
                
                // Drop old table
                $this->db->exec('DROP TABLE sessions');
            }
            
            // Rename new table
            $this->db->exec('ALTER TABLE sessions_new RENAME TO sessions');
            
            // Recreate indexes
            $this->db->exec('
                CREATE INDEX IF NOT EXISTS idx_sessions_date_status 
                ON sessions(session_date, status)
            ');
            
            $this->db->exec('
                CREATE INDEX IF NOT EXISTS idx_sessions_browser 
                ON sessions(browser_session_id, session_date, status)
            ');
        }
    }
}