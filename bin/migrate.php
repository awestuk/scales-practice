#!/usr/bin/env php
<?php
require __DIR__ . '/../vendor/autoload.php';

use App\Storage\Migrations;

try {
    echo "Running database migrations...\n";
    
    $migrations = new Migrations();
    $migrations->run();
    
    echo "Migrations completed successfully!\n";
    exit(0);
} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}