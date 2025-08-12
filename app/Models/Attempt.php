<?php
namespace App\Models;

use App\Storage\Db;

class Attempt
{
    public int $id;
    public int $session_id;
    public int $scale_id;
    public int $attempt_no;
    public string $outcome;
    public string $created_at;
    
    public static function record(int $sessionId, int $scaleId, int $attemptNo, string $outcome): self
    {
        $now = date('c');
        
        $stmt = Db::getInstance()->prepare('
            INSERT INTO attempts (session_id, scale_id, attempt_no, outcome, created_at)
            VALUES (?, ?, ?, ?, ?)
        ');
        $stmt->execute([$sessionId, $scaleId, $attemptNo, $outcome, $now]);
        
        $attempt = new self();
        $attempt->id = (int)Db::getInstance()->lastInsertId();
        $attempt->session_id = $sessionId;
        $attempt->scale_id = $scaleId;
        $attempt->attempt_no = $attemptNo;
        $attempt->outcome = $outcome;
        $attempt->created_at = $now;
        
        return $attempt;
    }
    
    public static function getSessionHistory(int $sessionId): array
    {
        $stmt = Db::getInstance()->prepare('
            SELECT 
                a.*,
                s.name as scale_name
            FROM attempts a
            JOIN scales s ON s.id = a.scale_id
            WHERE a.session_id = ?
            ORDER BY a.attempt_no DESC
        ');
        $stmt->execute([$sessionId]);
        return $stmt->fetchAll();
    }
}