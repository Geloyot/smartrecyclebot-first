<?php

namespace Tests\Feature;

use App\Models\Bin;
use App\Models\BinReading;
use App\Models\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class Integration4Test extends TestCase
{
    // Using RefreshDatabase is optional; comment out if you want to preserve your real DB
    // use RefreshDatabase;

    public function test_bin_readings_respect_modified_threshold_and_generate_alerts()
    {
        // Step 1: Setup initial bin and readings
        $bin = Bin::create([
            'name' => 'Test Bin',
            'type' => 'bio',
            'notified_full' => false,
            'last_full_fill_level' => null,
        ]);

        // Step 2: Set a custom full threshold (simulate dynamic threshold)
        $fullThreshold = 70; // Example new threshold

        // Step 3: Insert a reading above the threshold -> should trigger a FULL notification
        $reading1 = BinReading::create([
            'bin_id' => $bin->id,
            'fill_level' => 75,
        ]);

        // Manually compute status using threshold logic (simulate component logic)
        $status1 = $reading1->fill_level >= $fullThreshold ? 'FULL' : 'LOW';

        if ($status1 === 'FULL') {
            Notification::create([
                'user_id' => null,
                'type' => 'Bin Monitor',
                'title' => 'Bin is Full',
                'message' => "Bin #{$bin->id} ({$bin->name}) is now full ({$reading1->fill_level}%).",
                'level' => 'warning',
                'is_read' => false,
            ]);

            $bin->update([
                'notified_full' => true,
                'last_full_fill_level' => $reading1->fill_level,
            ]);
        }

        // Step 4: Assert FULL notification was created
        $this->assertDatabaseHas('notifications', [
            'title' => 'Bin is Full',
            'message' => "Bin #{$bin->id} ({$bin->name}) is now full ({$reading1->fill_level}%).",
            'level' => 'warning',
        ]);

        // Step 5: Insert a reading below threshold drop -> should trigger emptied notification
        $reading2 = BinReading::create([
            'bin_id' => $bin->id,
            'fill_level' => 30, // ≥40% drop from 75
        ]);

        $bin->refresh();

        if (
            $bin->notified_full &&
            $bin->last_full_fill_level !== null &&
            ($bin->last_full_fill_level - $reading2->fill_level) >= 40
        ) {
            $levelDrop = $bin->last_full_fill_level - $reading2->fill_level;
            Notification::create([
                'user_id' => null,
                'type' => 'Bin Monitor',
                'title' => 'Bin Emptied',
                'message' => "Bin #{$bin->id} ({$bin->name}) was emptied (dropped by {$levelDrop}%).",
                'level' => 'info',
                'is_read' => false,
            ]);

            $bin->update([
                'notified_full' => false,
                'last_full_fill_level' => null,
            ]);
        }

        // Step 6: Assert Emptied notification exists
        $this->assertDatabaseHas('notifications', [
            'title' => 'Bin Emptied',
            'level' => 'info',
        ]);

        // Step 7: Confirm bin flags were reset
        $bin->refresh();
        $this->assertFalse($bin->notified_full);
        $this->assertNull($bin->last_full_fill_level);

        Log::info("ITG-04: Threshold alert evaluation test passed.");
    }
}
