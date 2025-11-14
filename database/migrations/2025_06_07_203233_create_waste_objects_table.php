<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('waste_objects', function (Blueprint $table) {
            $table->id();
            $table->string('model_name')->nullable();
            $table->string('classification'); // e.g., "Biodegradable"
            $table->float('score', 4)->nullable();   // confidence; format: up to 4 decimals
            $table->timestamps();

            $table->unsignedBigInteger('bin_id')->nullable()->index();
            $table->foreign('bin_id')->references('id')->on('bins')->onDelete('set null');

            // Indexes for common queries
            $table->index(['classification']);
            $table->index(['created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('waste_objects');
    }
};
