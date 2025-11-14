<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;

class Integration2Test extends TestCase
{
    /**
     * Test Case: ITG-02
     * Module Names: Ultrasonic Sensor Integration and Real-time Bin Status Dashboard
     * Pre-condition: Verify if dashboard shows latest fill details in expected delay
     */
    public function test_dashboard_shows_latest_bin_fill()
    {
        // Use an existing user (role_id = 2) to access dashboard
        $user = User::where('role_id', 2)->first();
        $this->actingAs($user);

        // Step 1: Insert simulated bin readings
        $bioFill = 75;
        $nonBioFill = 60;
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

        // Step 3: Assert the response is 200 and contains the latest fill levels
        $response->assertStatus(200)
                 ->assertSee((string) $bioFill)
                 ->assertSee((string) $nonBioFill);

        // Optional: log timestamp for verification
        Log::info("Dashboard tested with readings at {$timestamp}");
    }
}
