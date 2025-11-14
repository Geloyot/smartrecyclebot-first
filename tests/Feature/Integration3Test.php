<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;

class Integration3Test extends TestCase
{
    /**
     * Test Case: ITG-03
     * Module Names: Ultrasonic Sensor Integration and Automatic Full Bin Alert
     * Pre-condition: Verify if dashboard shows latest fill details in expected delay
     */
    public function test_dashboard_shows_full_bin_alert()
    {
        // Use an existing user (role_id = 2) to access dashboard
        $user = User::where('role_id', 2)->first();
        $this->actingAs($user);

        // Step 1: Insert simulated bin readings that exceed full threshold
        $bioFill = 100;    // Full
        $nonBioFill = 95;  // Near full, adjust based on your threshold
        $timestamp = now();

        DB::table('bin_readings')->insert([
            [
                'bin_id'     => 1,
                'fill_level' => $bioFill,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'bin_id'     => 2,
                'fill_level' => $nonBioFill,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
        ]);

        // Step 2: Request the dashboard page
        $response = $this->get('/dashboard');

        // Step 3: Assert the response is 200 and shows full bin alert
        $response->assertStatus(200)
                 ->assertSee('🔴')          // Check if the red circle emoji indicator appears
                 ->assertSee((string) $bioFill);

        // Optional: verify near-full bin is also displayed correctly
        $response->assertSee((string) $nonBioFill);

        // Optional: log timestamp for verification
        Log::info("Full bin alert tested at {$timestamp}");
    }
}
