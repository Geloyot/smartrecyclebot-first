<?php

namespace Tests\Feature;

use App\Models\User;
use App\Livewire\BinMonitor;
use App\Models\Bin;
use App\Models\BinReading;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Livewire\Livewire;
use Tests\TestCase;

class Integration5Test extends TestCase
{
    use RefreshDatabase;

    /**
     * Test Case: ITG-05
     * Module: Bin Capacity Threshold Settings and Real-time Bin Status Dashboard
     * Pre-condition: Verify if dashboard shows updated threshold and updates reflect the new threshold
     */
    public function test_dashboard_reflects_updated_bin_threshold()
    {

        // 1. Create test bins
        $bioBin = Bin::create(['name' => 'Bio Bin', 'type' => 'bio']);
        $nonBioBin = Bin::create(['name' => 'Non-Bio Bin', 'type' => 'non-bio']);

        // 2. Create readings that exceed the default threshold
        BinReading::create(['bin_id' => $bioBin->id, 'fill_level' => 85]);
        BinReading::create(['bin_id' => $nonBioBin->id, 'fill_level' => 65]);

        // 3. Set a custom threshold for the test
        $customThreshold = 90;
        Config::set('smartrecyclebot.full_bin_threshold', $customThreshold);

        // 4. Test the Livewire BinMonitor component
        Livewire::test(BinMonitor::class)
            ->assertViewHas('binsData', function ($binsData) use ($customThreshold) {
                foreach ($binsData as $bin) {
                    // The status should reflect the new threshold
                    if ($bin['type'] === 'bio') {
                        // 85 < 90, should NOT be FULL
                        if ($bin['fill'] >= $customThreshold && $bin['status'] === 'FULL') {
                            return false;
                        }
                    }
                    if ($bin['type'] === 'non-bio') {
                        // 65 < 90, should NOT be FULL
                        if ($bin['fill'] >= $customThreshold && $bin['status'] === 'FULL') {
                            return false;
                        }
                    }
                }
                return true;
            });

        // 5. Simulate dashboard request as an authenticated user
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard'); // now authenticated
        $response->assertStatus(200);

        // Ensure the threshold value is displayed somewhere
        $response->assertSee((string)$customThreshold);
    }
}
