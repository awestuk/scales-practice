<?php
namespace App\Domain;

use App\Models\Session;
use App\Models\Scale;
use App\Storage\Db;

class SessionService
{
    public function getOrCreateActiveSession(): Session
    {
        // Check for existing active session today
        $session = Session::findActive();
        
        if ($session) {
            return $session;
        }
        
        // Create new session with current config
        $requiredSuccesses = $this->getRequiredSuccesses();
        $session = Session::createNew($requiredSuccesses);
        
        // Initialize all scales for the session
        $scales = Scale::findAll();
        $scaleIds = array_map(fn($s) => $s->id, $scales);
        $session->initializeScales($scaleIds);
        
        return $session;
    }
    
    public function resetSession(): Session
    {
        // End current session if exists
        $current = Session::findActive();
        if ($current) {
            $current->end();
        }
        
        // Create new session
        return $this->getOrCreateActiveSession();
    }
    
    public function canStartNewDay(): bool
    {
        $today = date('Y-m-d');
        $stmt = Db::getInstance()->prepare('
            SELECT COUNT(*) FROM sessions WHERE session_date = ?
        ');
        $stmt->execute([$today]);
        
        // Can start new day if no sessions today
        return $stmt->fetchColumn() == 0;
    }
    
    public function getRequiredSuccesses(): int
    {
        $stmt = Db::getInstance()->prepare('
            SELECT value FROM config WHERE key = "required_successes"
        ');
        $stmt->execute();
        $value = $stmt->fetchColumn();
        
        return $value ? (int)$value : 3;
    }
    
    public function updateConfig(string $key, string $value): void
    {
        $stmt = Db::getInstance()->prepare('
            INSERT OR REPLACE INTO config (key, value) VALUES (?, ?)
        ');
        $stmt->execute([$key, $value]);
    }
    
    public function getConfig(): array
    {
        $stmt = Db::getInstance()->query('
            SELECT key, value FROM config
        ');
        
        $config = [];
        while ($row = $stmt->fetch()) {
            $config[$row['key']] = $row['value'];
        }
        
        return $config;
    }
    
    public function reseedActiveSession(array $scaleIds): void
    {
        $session = Session::findActive();
        if (!$session) {
            return;
        }
        
        // Clear existing scale states
        $stmt = Db::getInstance()->prepare('
            DELETE FROM session_scale_state WHERE session_id = ?
        ');
        $stmt->execute([$session->id]);
        
        // Re-initialize with new scales
        $session->initializeScales($scaleIds);
    }
}