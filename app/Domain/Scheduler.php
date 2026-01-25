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
                s.notes,
                s.type
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
            $stmt = $db->prepare('SELECT value FROM config WHERE key = ?');
            $stmt->execute(['allow_repeat_when_last_only']);
            $allowRepeat = $stmt->fetchColumn() === '1';
        }
        
        // Filter out previous scale if not allowing repeat
        if ($prevScaleId && !$allowRepeat && count($candidates) > 1) {
            $candidates = array_filter($candidates, fn($c) => $c['scale_id'] != $prevScaleId);
        }
        
        // Use weighted random selection
        $weights = [];
        $totalWeight = 0;
        
        foreach ($candidates as $index => $candidate) {
            // Base weight: 1 for never attempted, 2 for attempted scales
            $weight = is_null($candidate['last_shown_at']) ? 1.0 : 2.0;
            
            // Bonus weight for scales needing more work (0.1 per remaining token)
            // This gives a slight preference to scales that need more practice
            $weight += $candidate['tokens_remaining'] * 0.1;
            
            // Recency bonus: scales shown longer ago get a small boost
            // This prevents any scale from being ignored too long
            if (!is_null($candidate['last_shown_at'])) {
                $lastShownValues = array_filter(array_column($candidates, 'last_shown_at'), fn($v) => !is_null($v));
                if (!empty($lastShownValues)) {
                    $maxLastShown = max($lastShownValues);
                    $ageRatio = 1 - ($candidate['last_shown_at'] / ($maxLastShown + 1));
                    $weight += $ageRatio * 0.5; // Up to 0.5 bonus for oldest scales
                }
            }
            
            $weights[$index] = $weight;
            $totalWeight += $weight;
        }
        
        // Select scale using weighted random
        $random = mt_rand() / mt_getrandmax() * $totalWeight;
        $cumulative = 0;
        
        foreach ($weights as $index => $weight) {
            $cumulative += $weight;
            if ($random <= $cumulative) {
                return $candidates[$index];
            }
        }
        
        // Fallback (should never reach here)
        return $candidates[0] ?? null;
    }
    
    public function updateLastShown(int $sessionId, int $scaleId, int $attemptNo): void
    {
        $stmt = Db::getInstance()->prepare('
            UPDATE session_scale_state
            SET last_shown_at = ?
            WHERE session_id = ? AND scale_id = ?
        ');
        $stmt->execute([time(), $sessionId, $scaleId]);
    }
    
    public function recordOutcome(int $sessionId, int $scaleId, string $outcome, int $requiredSuccesses): void
    {
        $db = Db::getInstance();
        
        if ($outcome === 'success') {
            // Decrement tokens
            $stmt = $db->prepare('
                UPDATE session_scale_state
                SET
                    tokens_remaining = CASE WHEN tokens_remaining > 0 THEN tokens_remaining - 1 ELSE 0 END,
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