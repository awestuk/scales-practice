<?php
namespace Migrations;

use App\Storage\Migration;

class Migration006AddScaleTypes extends Migration
{
    public function getVersion(): string
    {
        return '006';
    }

    public function getName(): string
    {
        return 'Add scale types column';
    }

    public function up(): void
    {
        // Add type column with default 'Other'
        $this->db->exec("
            ALTER TABLE scales ADD COLUMN type TEXT DEFAULT 'Other'
        ");

        // Auto-categorize existing scales based on name patterns
        // Order matters - more specific patterns first

        // Third Apart scales
        $this->db->exec("
            UPDATE scales SET type = 'Third Apart'
            WHERE name LIKE '%Third Apart%' OR name LIKE '%Thirds Apart%'
        ");

        // Minor Harmonic scales
        $this->db->exec("
            UPDATE scales SET type = 'Minor Harmonic'
            WHERE name LIKE '%Minor Harmonic%' OR name LIKE '%Harmonic Minor%'
        ");

        // Minor Melodic scales
        $this->db->exec("
            UPDATE scales SET type = 'Minor Melodic'
            WHERE name LIKE '%Minor Melodic%' OR name LIKE '%Melodic Minor%'
        ");

        // Major scales (but not if already categorized)
        $this->db->exec("
            UPDATE scales SET type = 'Major Scale'
            WHERE name LIKE '%Major%' AND type = 'Other'
        ");

        // Contrary Motion
        $this->db->exec("
            UPDATE scales SET type = 'Contrary Motion'
            WHERE name LIKE '%Contrary%' AND type = 'Other'
        ");

        // Arpeggios
        $this->db->exec("
            UPDATE scales SET type = 'Arpeggio'
            WHERE name LIKE '%Arpeggio%' AND type = 'Other'
        ");

        // Dominant Seventh
        $this->db->exec("
            UPDATE scales SET type = 'Dominant Seventh'
            WHERE (name LIKE '%Dominant%' OR name LIKE '%Dom7%' OR name LIKE '%7th%') AND type = 'Other'
        ");
    }
}
