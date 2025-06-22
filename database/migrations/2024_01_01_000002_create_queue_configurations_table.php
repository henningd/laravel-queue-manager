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
        Schema::create('queue_configurations', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('display_name');
            $table->text('description')->nullable();
            $table->integer('priority')->default(0);
            $table->boolean('is_active')->default(true);
            $table->integer('max_jobs_per_minute')->default(0); // 0 = unbegrenzt
            $table->integer('retry_delay')->default(0); // Sekunden
            $table->json('allowed_job_types')->nullable(); // Welche Job-Typen erlaubt sind
            $table->json('configuration')->nullable(); // Zusätzliche Konfiguration
            $table->timestamps();

            $table->index(['is_active', 'priority']);
            $table->index(['name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('queue_configurations');
    }
};