<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Safely drops obsolete, unused machine events and status log tables.
     */
    public function up(): void
    {
        Schema::dropIfExists('machine_status_logs');
        Schema::dropIfExists('machine_events');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('machine_events')) {
            Schema::create('machine_events', function (Blueprint $table) {
                $table->id();
                $table->foreignId('machine_id')->constrained()->cascadeOnDelete();
                $table->string('event_type', 50);
                $table->timestamp('event_time');
                $table->json('payload')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('machine_status_logs')) {
            Schema::create('machine_status_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('machine_id')->constrained()->cascadeOnDelete();
                $table->string('status', 30);
                $table->timestamp('changed_at');
                $table->timestamps();
            });
        }
    }
};
