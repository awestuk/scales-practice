<?php
namespace App\Domain;

use App\Storage\Db;

class StatsService
{
    public function getSessionStats(int $sessionId): array
    {
        $db = Db::getInstance();
        
        // Get overall session stats
        $stmt = $db->prepare('
            SELECT 
                COUNT(DISTINCT scale_id) as total_scales,
                SUM(CASE WHEN tokens_remaining = 0 THEN 1 ELSE 0 END) as completed_scales,
                SUM(successes) as total_successes,
                SUM(failures) as total_failures
            FROM session_scale_state
            WHERE session_id = ?
        ');
        $stmt->execute([$sessionId]);
        $overall = $stmt->fetch();
        
        // Get current attempt number
        $stmt = $db->prepare('
            SELECT COALESCE(MAX(attempt_no), 0) as current_attempt
            FROM attempts WHERE session_id = ?
        ');
        $stmt->execute([$sessionId]);
        $overall['attempt_no'] = (int)$stmt->fetchColumn();
        
        // Get per-scale stats
        $stmt = $db->prepare('
            SELECT
                sss.*,
                s.name as scale_name,
                s.type as scale_type
            FROM session_scale_state sss
            JOIN scales s ON s.id = sss.scale_id
            WHERE sss.session_id = ?
            ORDER BY s.type, s.name
        ');
        $stmt->execute([$sessionId]);
        $scales = $stmt->fetchAll();
        
        return [
            'overall' => $overall,
            'scales' => $scales
        ];
    }
    
    public function isSessionComplete(int $sessionId): bool
    {
        $stmt = Db::getInstance()->prepare('
            SELECT COUNT(*) FROM session_scale_state
            WHERE session_id = ? AND tokens_remaining > 0
        ');
        $stmt->execute([$sessionId]);
        
        return $stmt->fetchColumn() == 0;
    }
    
    public function getRecentAttempts(int $sessionId, int $limit = 10): array
    {
        $stmt = Db::getInstance()->prepare('
            SELECT 
                a.*,
                s.name as scale_name
            FROM attempts a
            JOIN scales s ON s.id = a.scale_id
            WHERE a.session_id = ?
            ORDER BY a.attempt_no DESC
            LIMIT ?
        ');
        $stmt->execute([$sessionId, $limit]);
        
        return $stmt->fetchAll();
    }
}