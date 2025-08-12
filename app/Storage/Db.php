<?php
namespace App\Storage;

use PDO;
use PDOException;

class Db
{
    private static ?PDO $instance = null;
    
    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            try {
                $dbPath = dirname(__DIR__, 2) . '/data/app.db';
                $dir = dirname($dbPath);
                
                if (!is_dir($dir)) {
                    mkdir($dir, 0755, true);
                }
                
                self::$instance = new PDO(
                    'sqlite:' . $dbPath,
                    null,
                    null,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false
                    ]
                );
                
                // Enable WAL mode for better concurrency
                self::$instance->exec('PRAGMA journal_mode=WAL');
                self::$instance->exec('PRAGMA synchronous=NORMAL');
                
            } catch (PDOException $e) {
                throw new \RuntimeException('Failed to connect to database: ' . $e->getMessage());
            }
        }
        
        return self::$instance;
    }
    
    public static function beginTransaction(): void
    {
        self::getInstance()->beginTransaction();
    }
    
    public static function commit(): void
    {
        self::getInstance()->commit();
    }
    
    public static function rollback(): void
    {
        self::getInstance()->rollBack();
    }
    
    /**
     * Set instance for testing purposes
     */
    public static function setInstance(?PDO $instance): void
    {
        self::$instance = $instance;
    }
}