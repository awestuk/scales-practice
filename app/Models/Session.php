<?php
namespace App\Models;

use App\Storage\Db;
use PDO;

class Session
{
    public int $id;
    public string $session_date;
    public string $started_at;
    public ?string $ended_at;
    public int $required_successes;
    public string $status;
    
    public static function findActive(): ?self
    {
        $today = date('Y-m-d');
        $stmt = Db::getInstance()->prepare('
            SELECT * FROM sessions 
            WHERE session_date = ? AND status = "active"
            LIMIT 1
        ');
        $stmt->execute([$today]);
        $stmt->setFetchMode(PDO::FETCH_CLASS, self::class);
        return $stmt->fetch() ?: null;
    }
    
    public static function find(int $id): ?self
    {
        $stmt = Db::getInstance()->prepare('
            SELECT * FROM sessions WHERE id = ?
        ');
        $stmt->execute([$id]);
        $stmt->setFetchMode(PDO::FETCH_CLASS, self::class);
        return $stmt->fetch() ?: null;
    }
    
    public static function createNew(int $requiredSuccesses): self
    {
        $now = date('c');
        $today = date('Y-m-d');
        
        $stmt = Db::getInstance()->prepare('
            INSERT INTO sessions (session_date, started_at, required_successes, status)
            VALUES (?, ?, ?, "active")
        ');
        $stmt->execute([$today, $now, $requiredSuccesses]);
        
        $session = new self();
        $session->id = (int)Db::getInstance()->lastInsertId();
        $session->session_date = $today;
        $session->started_at = $now;
        $session->ended_at = null;
        $session->required_successes = $requiredSuccesses;
        $session->status = 'active';
        
        return $session;
    }
    
    public function end(): void
    {
        $this->ended_at = date('c');
        $this->status = 'completed';
        
        $stmt = Db::getInstance()->prepare('
            UPDATE sessions SET ended_at = ?, status = ? WHERE id = ?
        ');
        $stmt->execute([$this->ended_at, $this->status, $this->id]);
    }
    
    public function initializeScales(array $scaleIds): void
    {
        $stmt = Db::getInstance()->prepare('
            INSERT INTO session_scale_state 
            (session_id, scale_id, tokens_remaining, successes, failures)
            VALUES (?, ?, ?, 0, 0)
        ');
        
        foreach ($scaleIds as $scaleId) {
            $stmt->execute([$this->id, $scaleId, $this->required_successes]);
        }
    }
    
    public function getScaleStates(): array
    {
        $stmt = Db::getInstance()->prepare('
            SELECT 
                sss.*,
                s.name as scale_name,
                s.notes as scale_notes
            FROM session_scale_state sss
            JOIN scales s ON s.id = sss.scale_id
            WHERE sss.session_id = ?
            ORDER BY s.name
        ');
        $stmt->execute([$this->id]);
        return $stmt->fetchAll();
    }
    
    public function getStats(): array
    {
        $stmt = Db::getInstance()->prepare('
            SELECT 
                COUNT(DISTINCT scale_id) as total_scales,
                SUM(CASE WHEN tokens_remaining = 0 THEN 1 ELSE 0 END) as completed_scales,
                SUM(successes) as total_successes,
                SUM(failures) as total_failures
            FROM session_scale_state
            WHERE session_id = ?
        ');
        $stmt->execute([$this->id]);
        return $stmt->fetch();
    }
    
    public function getNextAttemptNo(): int
    {
        $stmt = Db::getInstance()->prepare('
            SELECT COALESCE(MAX(attempt_no), 0) + 1 as next_no
            FROM attempts WHERE session_id = ?
        ');
        $stmt->execute([$this->id]);
        return (int)$stmt->fetchColumn();
    }
}