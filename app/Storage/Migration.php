<?php
namespace App\Storage;

use PDO;

abstract class Migration
{
    protected PDO $db;
    
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }
    
    /**
     * Get the migration version number
     */
    abstract public function getVersion(): string;
    
    /**
     * Get a descriptive name for this migration
     */
    abstract public function getName(): string;
    
    /**
     * Run the migration
     */
    abstract public function up(): void;
    
    /**
     * Reverse the migration (optional)
     */
    public function down(): void
    {
        // Override in child classes if rollback is needed
        throw new \RuntimeException("Rollback not implemented for migration: " . $this->getName());
    }
    
    /**
     * Helper to check if a table exists
     */
    protected function tableExists(string $tableName): bool
    {
        $stmt = $this->db->prepare("
            SELECT name FROM sqlite_master 
            WHERE type='table' AND name=?
        ");
        $stmt->execute([$tableName]);
        return $stmt->fetch() !== false;
    }
    
    /**
     * Helper to check if a column exists
     */
    protected function columnExists(string $tableName, string $columnName): bool
    {
        $stmt = $this->db->query("PRAGMA table_info($tableName)");
        $columns = $stmt->fetchAll();
        
        foreach ($columns as $column) {
            if ($column['name'] === $columnName) {
                return true;
            }
        }
        
        return false;
    }
}