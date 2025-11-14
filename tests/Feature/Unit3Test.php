<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

class Unit3Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a minimal classifications table for this test
        Schema::create('classifications', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('label', 128);
            $table->float('confidence', 8, 4);
            $table->json('bbox')->nullable();
            $table->timestamps();
        });
    }

    public function test_detection_model_creates_new_classification_record()
    {
        // Locate Python binary
        $pythonBinary = trim(shell_exec('which python3 || which python')) ?: null;
        if (! $pythonBinary) {
            $this->markTestSkipped('Python binary not found in PATH; skipping model inference test.');
            return;
        }

        // Candidate model paths
        $candidates = [
            base_path('best.pt'),
            base_path('python/best.pt'),
        ];
        $modelPath = null;
        foreach ($candidates as $c) {
            if (file_exists($c)) { $modelPath = $c; break; }
        }
        if (! $modelPath) {
            $this->markTestSkipped('Model file best.pt not found in project root or python/; skipping model inference test.');
            return;
        }

        // Candidate image path
        $imagePath = base_path('python/images/test1.jpg');
        if (! file_exists($imagePath)) {
            $this->markTestSkipped("Test image not found at {$imagePath}; add one to run the inference test.");
            return;
        }

        // Run the artisan command which calls the python inference and persists result
        $exit = $this->artisan('classify:image', [
            'image' => $imagePath,
            '--model' => $modelPath,
        ])->run();

        $this->assertEquals(0, $exit, 'Artisan classify:image command should exit with code 0.');

        // Assert a classification record was created
        $record = DB::table('classifications')->orderBy('created_at', 'desc')->first();
        $this->assertNotNull($record, 'No classification record was created by the classify:image command.');

        // Basic validations on returned record
        $this->assertIsString($record->label);
        $this->assertNotEmpty($record->label, 'Classification label should not be empty.');
        $this->assertGreaterThanOrEqual(0.0, (float)$record->confidence, 'Confidence must be numeric.');
        $this->assertLessThanOrEqual(1.0, (float)$record->confidence, 'Confidence must be <= 1.0.');

        // if bbox exists, ensure valid JSON and keys
        if ($record->bbox) {
            $bbox = json_decode($record->bbox, true);
            $this->assertIsArray($bbox);
            $this->assertArrayHasKey('x', $bbox);
            $this->assertArrayHasKey('y', $bbox);
            $this->assertArrayHasKey('w', $bbox);
            $this->assertArrayHasKey('h', $bbox);
        }
    }
}
