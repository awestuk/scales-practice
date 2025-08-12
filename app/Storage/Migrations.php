<?php
namespace App\Storage;

use PDO;

class Migrations
{
    private PDO $db;
    
    public function __construct()
    {
        $this->db = Db::getInstance();
    }
    
    public function run(): void
    {
        $this->createTables();
        $this->migrateExistingTables();
        $this->seedData();
    }
    
    private function migrateExistingTables(): void
    {
        // Check if browser_session_id column exists in sessions table
        $stmt = $this->db->query("PRAGMA table_info(sessions)");
        $columns = $stmt->fetchAll();
        $hasBrowserSessionId = false;
        
        foreach ($columns as $column) {
            if ($column['name'] === 'browser_session_id') {
                $hasBrowserSessionId = true;
                break;
            }
        }
        
        // If column doesn't exist, we need to migrate the table
        if (!$hasBrowserSessionId && $this->tableExists('sessions')) {
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
            
            // Copy existing data with default browser_session_id
            $this->db->exec('
                INSERT INTO sessions_new (id, browser_session_id, session_date, started_at, ended_at, required_successes, status)
                SELECT id, "default", session_date, started_at, ended_at, required_successes, status
                FROM sessions
            ');
            
            // Drop old table and rename new one
            $this->db->exec('DROP TABLE sessions');
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
    
    private function tableExists(string $tableName): bool
    {
        $stmt = $this->db->prepare("
            SELECT name FROM sqlite_master 
            WHERE type='table' AND name=?
        ");
        $stmt->execute([$tableName]);
        return $stmt->fetch() !== false;
    }
    
    private function createTables(): void
    {
        // Scales table
        $this->db->exec('
            CREATE TABLE IF NOT EXISTS scales (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT UNIQUE NOT NULL,
                notes TEXT
            )
        ');
        
        // Config table
        $this->db->exec('
            CREATE TABLE IF NOT EXISTS config (
                key TEXT PRIMARY KEY,
                value TEXT
            )
        ');
        
        // Sessions table
        $this->db->exec('
            CREATE TABLE IF NOT EXISTS sessions (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                browser_session_id TEXT NOT NULL,
                session_date TEXT NOT NULL,
                started_at TEXT NOT NULL,
                ended_at TEXT,
                required_successes INTEGER NOT NULL,
                status TEXT CHECK(status IN ("active", "completed"))
            )
        ');
        
        // Create index for session lookups
        $this->db->exec('
            CREATE INDEX IF NOT EXISTS idx_sessions_date_status 
            ON sessions(session_date, status)
        ');
        
        // Create index for browser session lookups
        $this->db->exec('
            CREATE INDEX IF NOT EXISTS idx_sessions_browser 
            ON sessions(browser_session_id, session_date, status)
        ');
        
        // Session scale state table
        $this->db->exec('
            CREATE TABLE IF NOT EXISTS session_scale_state (
                session_id INTEGER NOT NULL,
                scale_id INTEGER NOT NULL,
                tokens_remaining INTEGER NOT NULL,
                successes INTEGER DEFAULT 0,
                failures INTEGER DEFAULT 0,
                last_shown_at INTEGER,
                PRIMARY KEY (session_id, scale_id),
                FOREIGN KEY (session_id) REFERENCES sessions(id) ON DELETE CASCADE,
                FOREIGN KEY (scale_id) REFERENCES scales(id) ON DELETE CASCADE
            )
        ');
        
        // Create index for scheduler queries
        $this->db->exec('
            CREATE INDEX IF NOT EXISTS idx_scale_state_scheduling 
            ON session_scale_state(session_id, tokens_remaining, last_shown_at)
        ');
        
        // Attempts table
        $this->db->exec('
            CREATE TABLE IF NOT EXISTS attempts (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                session_id INTEGER NOT NULL,
                scale_id INTEGER NOT NULL,
                attempt_no INTEGER NOT NULL,
                outcome TEXT CHECK(outcome IN ("success", "fail")),
                created_at TEXT NOT NULL,
                FOREIGN KEY (session_id) REFERENCES sessions(id) ON DELETE CASCADE,
                FOREIGN KEY (scale_id) REFERENCES scales(id) ON DELETE CASCADE
            )
        ');
        
        // Create index for attempt lookups
        $this->db->exec('
            CREATE INDEX IF NOT EXISTS idx_attempts_session 
            ON attempts(session_id, attempt_no)
        ');
    }
    
    private function seedData(): void
    {
        // Seed default config
        $stmt = $this->db->prepare('
            INSERT OR IGNORE INTO config (key, value) VALUES (?, ?)
        ');
        
        $stmt->execute(['required_successes', '3']);
        $stmt->execute(['allow_repeat_when_last_only', '1']);
        $stmt->execute(['show_notes', '1']);
        
        // Seed common major scales
        $scales = [
            ['C Major', 'C D E F G A B C'],
            ['G Major', 'G A B C D E F# G'],
            ['D Major', 'D E F# G A B C# D'],
            ['A Major', 'A B C# D E F# G# A'],
            ['E Major', 'E F# G# A B C# D# E'],
            ['B Major', 'B C# D# E F# G# A# B'],
            ['F Major', 'F G A Bb C D E F'],
            ['Bb Major', 'Bb C D Eb F G A Bb'],
            ['Eb Major', 'Eb F G Ab Bb C D Eb'],
            ['Ab Major', 'Ab Bb C Db Eb F G Ab'],
            ['Db Major', 'Db Eb F Gb Ab Bb C Db'],
            ['Gb Major', 'Gb Ab Bb Cb Db Eb F Gb']
        ];
        
        $stmt = $this->db->prepare('
            INSERT OR IGNORE INTO scales (name, notes) VALUES (?, ?)
        ');
        
        foreach ($scales as $scale) {
            $stmt->execute($scale);
        }
    }
}