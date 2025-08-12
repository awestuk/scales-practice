<?php
namespace Migrations;

use App\Storage\Migration;

class Migration004AddAdditionalScales extends Migration
{
    public function getVersion(): string
    {
        return '004';
    }
    
    public function getName(): string
    {
        return 'Add additional scales and third apart exercises';
    }
    
    public function up(): void
    {
        // Add new scales
        $scales = [
            // Note: Some scales already exist (E Major, G Major) but INSERT OR IGNORE handles this
            ['E Major', 'E F# G# A B C# D# E'],
            ['G Major', 'G A B C D E F# G'],
            ['C# Minor Harmonic', 'C# D# E F# G# A B# C#'],
            ['C# Minor Melodic', 'C# D# E F# G# A# B# C#'],
            ['E Minor Harmonic', 'E F# G A B C D# E'],
            ['E Minor Melodic', 'E F# G A B C# D# E'],
            ['G Minor Harmonic', 'G A Bb C D Eb F# G'],
            ['G Minor Melodic', 'G A Bb C D E F# G'],
            ['Bb Minor Harmonic', 'Bb C Db Eb F Gb A Bb'],
            ['Bb Minor Melodic', 'Bb C Db Eb F G A Bb'],
            
            // Third apart exercises
            ['Third Apart: Db Major', 'Db F, Eb G, F Ab, Gb Bb, Ab C, Bb Db, C Eb, Db'],
            ['Third Apart: E Major', 'E G#, F# A, G# B, A C#, B D#, C# E, D# F#, E'],
            ['Third Apart: G Major', 'G B, A C, B D, C E, D F#, E G, F# A, G'],
            ['Third Apart: Bb Major', 'Bb D, C Eb, D F, Eb G, F A, G Bb, A C, Bb'],
            ['Third Apart: C# Minor Harmonic', 'C# E, D# F#, E G#, F# A, G# B#, A C#, B# D#, C#'],
            ['Third Apart: E Minor Harmonic', 'E G, F# A, G B, A C, B D#, C E, D# F#, E'],
            ['Third Apart: G Minor Harmonic', 'G Bb, A C, Bb D, C Eb, D F#, Eb G, F# A, G'],
            ['Third Apart: Bb Minor Harmonic', 'Bb Db, C Eb, Db F, Eb Gb, F A, Gb Bb, A C, Bb']
        ];
        
        $stmt = $this->db->prepare('
            INSERT OR IGNORE INTO scales (name, notes) VALUES (?, ?)
        ');
        
        foreach ($scales as $scale) {
            $stmt->execute($scale);
        }
    }
}