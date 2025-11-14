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
        // database/migrations/xxxx_xx_xx_create_bin_readings_table.php
        Schema::create('bin_readings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bin_id');
            $table->float('fill_level');
            $table->timestamps();

            $table
                ->foreign('bin_id')
                ->references('id')
                ->on('bins')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bin_readings');
    }
};
