<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('oee_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_record_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('machine_id')->constrained()->cascadeOnDelete();
            $table->foreignId('production_line_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shift_id')->constrained()->cascadeOnDelete();
            $table->date('record_date');

            $table->decimal('availability', 8, 4)->default(0);
            $table->decimal('performance', 8, 4)->default(0);
            $table->decimal('quality', 8, 4)->default(0);
            $table->decimal('oee', 8, 4)->default(0);

            $table->json('six_big_losses_summary')->nullable();

            $table->timestamps();

            $table->index(['machine_id', 'record_date']);
            $table->index(['production_line_id', 'record_date']);
            $table->index(['shift_id', 'record_date']);
        });

        Schema::create('shift_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_line_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shift_id')->constrained()->cascadeOnDelete();
            $table->date('summary_date');

            $table->integer('total_target_qty')->default(0);
            $table->integer('total_actual_qty')->default(0);
            $table->integer('total_good_qty')->default(0);
            $table->integer('total_reject_qty')->default(0);

            $table->decimal('availability', 8, 4)->default(0);
            $table->decimal('performance', 8, 4)->default(0);
            $table->decimal('quality', 8, 4)->default(0);
            $table->decimal('oee', 8, 4)->default(0);

            $table->timestamps();

            $table->unique(['production_line_id', 'shift_id', 'summary_date'], 'line_shift_date_unique');
        });

        Schema::create('daily_production_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_line_id')->constrained()->cascadeOnDelete();
            $table->date('summary_date');

            $table->integer('total_target_qty')->default(0);
            $table->integer('total_actual_qty')->default(0);
            $table->integer('total_good_qty')->default(0);
            $table->integer('total_reject_qty')->default(0);

            $table->decimal('availability', 8, 4)->default(0);
            $table->decimal('performance', 8, 4)->default(0);
            $table->decimal('quality', 8, 4)->default(0);
            $table->decimal('oee', 8, 4)->default(0);

            $table->timestamps();

            $table->unique(['production_line_id', 'summary_date'], 'line_date_unique');
        });

        Schema::create('machine_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('machine_id')->constrained()->cascadeOnDelete();
            $table->enum('event_type', ['RUN', 'STOP', 'IDLE', 'ALARM', 'DEFECT', 'STATUS_CHANGE'])->default('STATUS_CHANGE');
            $table->dateTime('event_time');
            $table->json('payload')->nullable();
            $table->timestamps();

            $table->index(['machine_id', 'event_time']);
        });

        Schema::create('machine_status_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('machine_id')->constrained()->cascadeOnDelete();
            $table->string('status', 30);
            $table->dateTime('started_at');
            $table->dateTime('ended_at')->nullable();
            $table->timestamps();

            $table->index(['machine_id', 'started_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('machine_status_logs');
        Schema::dropIfExists('machine_events');
        Schema::dropIfExists('daily_production_summaries');
        Schema::dropIfExists('shift_summaries');
        Schema::dropIfExists('oee_records');
    }
};
