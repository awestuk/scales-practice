<?php
namespace Migrations;

use App\Storage\Migration;

class Migration003AddShowNotesConfig extends Migration
{
    public function getVersion(): string
    {
        return '003';
    }
    
    public function getName(): string
    {
        return 'Add show_notes configuration option';
    }
    
    public function up(): void
    {
        // Add show_notes config option if it doesn't exist
        $stmt = $this->db->prepare('
            SELECT COUNT(*) FROM config WHERE key = ?
        ');
        $stmt->execute(['show_notes']);
        
        if ($stmt->fetchColumn() == 0) {
            $stmt = $this->db->prepare('
                INSERT INTO config (key, value) VALUES (?, ?)
            ');
            $stmt->execute(['show_notes', '1']);
        }
    }
}