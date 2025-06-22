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
        Schema::create('queue_workers', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('queue')->default('default');
            $table->integer('processes')->default(1);
            $table->integer('timeout')->default(60);
            $table->integer('sleep')->default(3);
            $table->integer('max_tries')->default(3);
            $table->integer('memory')->default(128);
            $table->boolean('is_active')->default(true);
            $table->boolean('auto_restart')->default(true);
            $table->json('environment_variables')->nullable();
            $table->text('description')->nullable();
            $table->timestamp('last_started_at')->nullable();
            $table->timestamp('last_stopped_at')->nullable();
            $table->string('status')->default('stopped'); // stopped, running, failed
            $table->integer('pid')->nullable();
            $table->timestamps();

            $table->index(['queue', 'is_active']);
            $table->index(['status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('queue_workers');
    }
};