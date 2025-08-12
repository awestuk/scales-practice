#!/usr/bin/env php
<?php
require __DIR__ . '/../vendor/autoload.php';

use App\Storage\MigrationRunner;

try {
    echo "Running database migrations...\n";
    
    $runner = new MigrationRunner();
    $runner->run();
    
    $currentVersion = $runner->getCurrentVersion();
    if ($currentVersion) {
        echo "Database is at version: $currentVersion\n";
    }
    
    echo "Migrations completed successfully!\n";
    exit(0);
} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}