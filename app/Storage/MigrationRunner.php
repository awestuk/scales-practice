<?php
namespace App\Storage;

use PDO;

class MigrationRunner
{
    private PDO $db;
    private string $migrationsPath;
    
    public function __construct()
    {
        $this->db = Db::getInstance();
        $this->migrationsPath = dirname(__DIR__, 2) . '/migrations';
        $this->ensureMigrationsTable();
    }
    
    /**
     * Run all pending migrations
     */
    public function run(): void
    {
        $appliedMigrations = $this->getAppliedMigrations();
        $migrationFiles = $this->getMigrationFiles();
        
        foreach ($migrationFiles as $file) {
            $version = $this->extractVersion($file);
            
            if (!in_array($version, $appliedMigrations)) {
                $this->runMigration($file, $version);
            }
        }
    }
    
    /**
     * Ensure the migrations tracking table exists
     */
    private function ensureMigrationsTable(): void
    {
        $this->db->exec('
            CREATE TABLE IF NOT EXISTS migrations (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                version TEXT UNIQUE NOT NULL,
                name TEXT NOT NULL,
                applied_at TEXT NOT NULL
            )
        ');
    }
    
    /**
     * Get list of already applied migrations
     */
    private function getAppliedMigrations(): array
    {
        $stmt = $this->db->query('SELECT version FROM migrations ORDER BY version');
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    /**
     * Get all migration files from the migrations directory
     */
    private function getMigrationFiles(): array
    {
        if (!is_dir($this->migrationsPath)) {
            return [];
        }
        
        $files = glob($this->migrationsPath . '/*.php');
        sort($files); // Ensure they run in order
        return $files;
    }
    
    /**
     * Extract version number from filename (e.g., "001_initial_schema.php" -> "001")
     */
    private function extractVersion(string $filename): string
    {
        $basename = basename($filename);
        if (preg_match('/^(\d+)_/', $basename, $matches)) {
            return $matches[1];
        }
        throw new \RuntimeException("Invalid migration filename format: $basename");
    }
    
    /**
     * Run a single migration file
     */
    private function runMigration(string $file, string $version): void
    {
        require_once $file;
        
        $className = $this->getClassNameFromFile($file);
        $fullClassName = "\\Migrations\\" . $className;
        
        if (!class_exists($fullClassName)) {
            throw new \RuntimeException("Migration class not found: $fullClassName in file $file");
        }
        
        $migration = new $fullClassName($this->db);
        
        if (!$migration instanceof Migration) {
            throw new \RuntimeException("Class $fullClassName must extend Migration");
        }
        
        try {
            $this->db->beginTransaction();
            
            // Run the migration
            $migration->up();
            
            // Record that it was applied
            $stmt = $this->db->prepare('
                INSERT INTO migrations (version, name, applied_at) 
                VALUES (?, ?, ?)
            ');
            $stmt->execute([$version, $migration->getName(), date('c')]);
            
            $this->db->commit();
            
            echo "Applied migration $version: " . $migration->getName() . "\n";
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw new \RuntimeException(
                "Failed to apply migration $version: " . $e->getMessage()
            );
        }
    }
    
    /**
     * Convert filename to class name (e.g., "001_initial_schema.php" -> "Migration001InitialSchema")
     */
    private function getClassNameFromFile(string $file): string
    {
        $basename = basename($file, '.php');
        $parts = explode('_', $basename);
        
        $className = 'Migration' . $parts[0];
        for ($i = 1; $i < count($parts); $i++) {
            $className .= ucfirst($parts[$i]);
        }
        
        return $className;
    }
    
    /**
     * Get the current migration version
     */
    public function getCurrentVersion(): ?string
    {
        $stmt = $this->db->query('
            SELECT version FROM migrations 
            ORDER BY version DESC 
            LIMIT 1
        ');
        $version = $stmt->fetchColumn();
        return $version ?: null;
    }
    
    /**
     * Check if a specific migration has been applied
     */
    public function isApplied(string $version): bool
    {
        $stmt = $this->db->prepare('
            SELECT COUNT(*) FROM migrations WHERE version = ?
        ');
        $stmt->execute([$version]);
        return $stmt->fetchColumn() > 0;
    }
}