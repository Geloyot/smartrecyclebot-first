<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class Integration1Test extends TestCase
{
    // use RefreshDatabase;

    /**
     * Test Case: ITG-01
     * Module Names: Ultrasonic Sensor Integration and Bin Capacity Integration
     * Pre-condition: Verify if the system flags the bin as FULL and creates a record
     */
    public function test_bin_flags_full_and_creates_record()
    {
        // Pre-condition: bin_readings table is empty
        // $this->assertDatabaseCount('bin_readings', 0);

        // Action: simulate bin readings above "FULL" threshold
        // For example, assume 100 = FULL
        $fullBioLevel = 100;
        $fullNonBioLevel = 100;

        // Hit the bin-reading-read endpoint
        $response = $this->getJson("/api/bin-reading-read?bio={$fullBioLevel}&nonbio={$fullNonBioLevel}");

        // Assert response is successful
        $response->assertStatus(200)
                 ->assertJson(['status' => 'saved']);

        // Assert database entries were created
        $this->assertDatabaseHas('bin_readings', [
            'bin_id' => 1,
            'fill_level' => $fullBioLevel,
        ]);

        $this->assertDatabaseHas('bin_readings', [
            'bin_id' => 2,
            'fill_level' => $fullNonBioLevel,
        ]);

        // $this->assertDatabaseCount('bin_readings', 2);

        // Optional: if you have a 'status' column or flag for FULL
        // $this->assertDatabaseHas('bin_readings', [
        //     'bin_id' => 1,
        //     'status' => 'FULL',
        // ]);
    }
}
