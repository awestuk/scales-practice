<?php
namespace App\Domain;

use App\Models\Session;
use App\Models\Scale;
use App\Storage\Db;

class SessionService
{
    private function getBrowserSessionId(): string
    {
        if (!isset($_SESSION['browser_session_id'])) {
            $_SESSION['browser_session_id'] = bin2hex(random_bytes(16));
        }
        return $_SESSION['browser_session_id'];
    }
    
    public function getOrCreateActiveSession(): Session
    {
        $browserSessionId = $this->getBrowserSessionId();
        
        // Check for existing active session today for this browser session
        $session = Session::findActive($browserSessionId);
        
        if ($session) {
            return $session;
        }
        
        // Create new session with current config
        $requiredSuccesses = $this->getRequiredSuccesses();
        $session = Session::createNew($browserSessionId, $requiredSuccesses);
        
        // Initialize all scales for the session
        $scales = Scale::findAll();
        $scaleIds = array_map(fn($s) => $s->id, $scales);
        $session->initializeScales($scaleIds);
        
        return $session;
    }
    
    public function resetSession(): Session
    {
        $browserSessionId = $this->getBrowserSessionId();
        
        // End current session if exists
        $current = Session::findActive($browserSessionId);
        if ($current) {
            $current->end();
        }
        
        // Create new session
        return $this->getOrCreateActiveSession();
    }
    
    public function canStartNewDay(): bool
    {
        $browserSessionId = $this->getBrowserSessionId();
        $today = date('Y-m-d');
        $stmt = Db::getInstance()->prepare('
            SELECT COUNT(*) FROM sessions WHERE browser_session_id = ? AND session_date = ?
        ');
        $stmt->execute([$browserSessionId, $today]);
        
        // Can start new day if no sessions today for this browser session
        return $stmt->fetchColumn() == 0;
    }
    
    public function getRequiredSuccesses(): int
    {
        $stmt = Db::getInstance()->prepare('
            SELECT value FROM config WHERE key = "required_successes"
        ');
        $stmt->execute();
        $value = $stmt->fetchColumn();
        
        return $value ? (int)$value : 2;
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
        $browserSessionId = $this->getBrowserSessionId();
        $session = Session::findActive($browserSessionId);
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
    
    public function getShowNotes(): bool
    {
        $stmt = Db::getInstance()->prepare('
            SELECT value FROM config WHERE key = "show_notes"
        ');
        $stmt->execute();
        $value = $stmt->fetchColumn();
        
        return $value === '1';
    }
}