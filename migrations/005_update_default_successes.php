<?php
namespace Migrations;

use App\Storage\Migration;

class Migration005UpdateDefaultSuccesses extends Migration
{
    public function getVersion(): string
    {
        return '005';
    }
    
    public function getName(): string
    {
        return 'Update default required successes to 2';
    }
    
    public function up(): void
    {
        // Only update if the current value is still the old default (3)
        // This preserves any custom user settings
        $this->db->exec("
            UPDATE config 
            SET value = '2' 
            WHERE key = 'required_successes' 
            AND value = '3'
        ");
    }
    
    public function down(): void
    {
        // Revert to old default if needed
        $this->db->exec("
            UPDATE config 
            SET value = '3' 
            WHERE key = 'required_successes' 
            AND value = '2'
        ");
    }
}