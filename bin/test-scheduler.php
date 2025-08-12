#!/usr/bin/env php
<?php
/**
 * Test script for the weighted random scheduling algorithm
 * 
 * This simulates scale selection to verify:
 * 1. Attempted scales appear more frequently than unattempted ones
 * 2. No immediate repeats (unless only one scale left)
 * 3. All scales eventually get selected
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Domain\Scheduler;
use App\Storage\Db;

// Initialize database
$dbPath = __DIR__ . '/../data/test-scheduler.db';
if (file_exists($dbPath)) {
    unlink($dbPath);
}

$db = new PDO('sqlite:' . $dbPath);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Create minimal schema for testing
$db->exec('
    CREATE TABLE config (
        key TEXT PRIMARY KEY,
        value TEXT
    );
    
    CREATE TABLE scales (
        id INTEGER PRIMARY KEY,
        name TEXT NOT NULL,
        notes TEXT,
        browser_session_id TEXT DEFAULT "default"
    );
    
    CREATE TABLE sessions (
        id INTEGER PRIMARY KEY,
        required_successes INTEGER DEFAULT 3,
        browser_session_id TEXT DEFAULT "default"
    );
    
    CREATE TABLE session_scale_state (
        session_id INTEGER,
        scale_id INTEGER,
        tokens_remaining INTEGER,
        last_shown_at INTEGER,
        successes INTEGER DEFAULT 0,
        failures INTEGER DEFAULT 0,
        PRIMARY KEY (session_id, scale_id)
    );
');

// Insert test data
$db->exec("INSERT INTO config (key, value) VALUES ('allow_repeat_when_last_only', '1')");
$db->exec("INSERT INTO sessions (id, required_successes) VALUES (1, 3)");

// Create 10 test scales
for ($i = 1; $i <= 10; $i++) {
    $db->exec("INSERT INTO scales (id, name) VALUES ($i, 'Scale $i')");
    $db->exec("INSERT INTO session_scale_state (session_id, scale_id, tokens_remaining, last_shown_at) 
               VALUES (1, $i, 3, " . ($i <= 3 ? $i : 'NULL') . ")");
}

// Override Db instance for testing
Db::setInstance($db);

// Run simulation
$scheduler = new Scheduler();
$selections = [];
$prevScaleId = null;
$attemptNo = 1;

echo "Running 100 scale selections...\n\n";

for ($i = 0; $i < 100; $i++) {
    $scale = $scheduler->nextScale(1, $prevScaleId);
    
    if (!$scale) {
        echo "Session complete at attempt $i\n";
        break;
    }
    
    $scaleId = $scale['scale_id'];
    
    // Track selections
    if (!isset($selections[$scaleId])) {
        $selections[$scaleId] = 0;
    }
    $selections[$scaleId]++;
    
    // Check for immediate repeat
    if ($prevScaleId && $prevScaleId == $scaleId) {
        echo "WARNING: Immediate repeat detected! Scale $scaleId appeared twice in a row.\n";
    }
    
    // Update last shown
    $scheduler->updateLastShown(1, $scaleId, $attemptNo++);
    
    // Simulate random success/failure (70% success rate)
    if (rand(1, 10) <= 7) {
        $scheduler->recordOutcome(1, $scaleId, 'success', 3);
    } else {
        $scheduler->recordOutcome(1, $scaleId, 'fail', 3);
    }
    
    $prevScaleId = $scaleId;
}

// Analyze results
echo "\nSelection frequency analysis:\n";
echo "==============================\n";

ksort($selections);
$attemptedScales = [1, 2, 3]; // Scales with last_shown_at set initially
$unattemptedScales = range(4, 10);

$attemptedTotal = 0;
$unattemptedTotal = 0;

foreach ($selections as $scaleId => $count) {
    $status = in_array($scaleId, $attemptedScales) ? 'initially attempted' : 'initially unattempted';
    echo "Scale $scaleId: $count times ($status)\n";
    
    if (in_array($scaleId, $attemptedScales)) {
        $attemptedTotal += $count;
    } else {
        $unattemptedTotal += $count;
    }
}

echo "\n";
echo "Average selections per initially attempted scale: " . 
     round($attemptedTotal / count($attemptedScales), 2) . "\n";
echo "Average selections per initially unattempted scale: " . 
     round($unattemptedTotal / count($unattemptedScales), 2) . "\n";

$ratio = $attemptedTotal / count($attemptedScales) / ($unattemptedTotal / count($unattemptedScales));
echo "\nRatio (should be around 2.0): " . round($ratio, 2) . "\n";

if ($ratio > 1.5 && $ratio < 2.5) {
    echo "✓ Weighted selection working correctly!\n";
} else {
    echo "✗ Weighted selection may need adjustment.\n";
}

// Cleanup
unlink($dbPath);