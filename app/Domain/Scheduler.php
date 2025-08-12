<?php
namespace App\Domain;

use App\Storage\Db;

class Scheduler
{
    public function nextScale(int $sessionId, ?int $prevScaleId = null): ?array
    {
        $db = Db::getInstance();
        
        // Get all scales with tokens remaining > 0
        $stmt = $db->prepare('
            SELECT 
                sss.scale_id,
                sss.tokens_remaining,
                sss.last_shown_at,
                s.name,
                s.notes
            FROM session_scale_state sss
            JOIN scales s ON s.id = sss.scale_id
            WHERE sss.session_id = ? AND sss.tokens_remaining > 0
        ');
        $stmt->execute([$sessionId]);
        $candidates = $stmt->fetchAll();
        
        if (empty($candidates)) {
            return null; // Session complete
        }
        
        // Check if we should allow immediate repeat
        $allowRepeat = false;
        if (count($candidates) === 1) {
            $stmt = $db->query('SELECT value FROM config WHERE key = "allow_repeat_when_last_only"');
            $allowRepeat = $stmt->fetchColumn() === '1';
        }
        
        // Filter out previous scale if not allowing repeat
        if ($prevScaleId && !$allowRepeat && count($candidates) > 1) {
            $candidates = array_filter($candidates, fn($c) => $c['scale_id'] != $prevScaleId);
        }
        
        // Sort by scheduling priority
        usort($candidates, function($a, $b) {
            // 1. Never shown first (NULL last_shown_at)
            if (is_null($a['last_shown_at']) && !is_null($b['last_shown_at'])) return -1;
            if (!is_null($a['last_shown_at']) && is_null($b['last_shown_at'])) return 1;
            
            // 2. Oldest last_shown_at
            if ($a['last_shown_at'] != $b['last_shown_at']) {
                return ($a['last_shown_at'] ?? 0) <=> ($b['last_shown_at'] ?? 0);
            }
            
            // 3. Higher tokens_remaining
            if ($a['tokens_remaining'] != $b['tokens_remaining']) {
                return $b['tokens_remaining'] <=> $a['tokens_remaining'];
            }
            
            // 4. Random tiebreak
            return rand(-1, 1);
        });
        
        return $candidates[0] ?? null;
    }
    
    public function updateLastShown(int $sessionId, int $scaleId, int $attemptNo): void
    {
        $stmt = Db::getInstance()->prepare('
            UPDATE session_scale_state 
            SET last_shown_at = ? 
            WHERE session_id = ? AND scale_id = ?
        ');
        $stmt->execute([$attemptNo, $sessionId, $scaleId]);
    }
    
    public function recordOutcome(int $sessionId, int $scaleId, string $outcome, int $requiredSuccesses): void
    {
        $db = Db::getInstance();
        
        if ($outcome === 'success') {
            // Decrement tokens
            $stmt = $db->prepare('
                UPDATE session_scale_state 
                SET 
                    tokens_remaining = MAX(0, tokens_remaining - 1),
                    successes = successes + 1
                WHERE session_id = ? AND scale_id = ?
            ');
        } else {
            // Reset tokens to required_successes
            $stmt = $db->prepare('
                UPDATE session_scale_state 
                SET 
                    tokens_remaining = ?,
                    failures = failures + 1
                WHERE session_id = ? AND scale_id = ?
            ');
            $stmt->execute([$requiredSuccesses, $sessionId, $scaleId]);
            return;
        }
        
        $stmt->execute([$sessionId, $scaleId]);
    }
}