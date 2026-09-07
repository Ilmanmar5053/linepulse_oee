<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('production_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_line_id')->constrained()->cascadeOnDelete();
            $table->string('plan_number', 50)->unique();
            $table->date('start_date');
            $table->date('end_date');
            $table->string('status', 30)->default('SCHEDULED');
            $table->timestamps();
        });

        Schema::create('production_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_plan_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('order_number', 50)->unique();
            $table->integer('target_quantity');
            $table->date('due_date')->nullable();
            $table->string('status', 30)->default('RELEASED');
            $table->timestamps();
        });

        Schema::create('production_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('production_line_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shift_id')->constrained()->cascadeOnDelete();
            $table->date('run_date');
            $table->string('status', 30)->default('RUNNING');
            $table->timestamps();
        });

        Schema::create('production_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('production_line_id')->constrained()->cascadeOnDelete();
            $table->foreignId('machine_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shift_id')->constrained()->cascadeOnDelete();
            $table->foreignId('operator_id')->nullable()->constrained()->nullOnDelete();

            $table->date('production_date');

            $table->integer('planned_production_time')->default(480)->comment('Minutes');
            $table->integer('planned_downtime')->default(60)->comment('Minutes');
            $table->integer('available_production_time')->default(420)->comment('Minutes');

            $table->integer('run_time')->default(0)->comment('Minutes');
            $table->integer('downtime')->default(0)->comment('Minutes');
            $table->integer('idle_time')->default(0)->comment('Minutes');

            $table->decimal('ideal_cycle_time', 10, 4)->default(0)->comment('Seconds per unit');
            $table->decimal('actual_cycle_time', 10, 4)->default(0)->comment('Seconds per unit');

            $table->integer('target_quantity')->default(0);
            $table->integer('total_quantity')->default(0);
            $table->integer('good_quantity')->default(0);
            $table->integer('reject_quantity')->default(0);
            $table->integer('scrap_quantity')->default(0);

            $table->decimal('production_rate', 10, 2)->default(0)->comment('Units per hour');

            $table->enum('status', ['DRAFT', 'IN_PROGRESS', 'COMPLETED', 'CLOSED'])->default('COMPLETED');

            $table->timestamps();
            $table->softDeletes();

            $table->index(['production_line_id', 'production_date']);
            $table->index(['machine_id', 'production_date']);
            $table->index(['shift_id', 'production_date']);
        });

        Schema::create('downtimes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_record_id')->constrained()->cascadeOnDelete();
            $table->foreignId('machine_id')->constrained()->cascadeOnDelete();
            $table->foreignId('production_line_id')->constrained()->cascadeOnDelete();

            $table->dateTime('start_time');
            $table->dateTime('end_time')->nullable();
            $table->decimal('duration_minutes', 10, 2)->default(0);

            $table->foreignId('downtime_category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('downtime_reason_id')->nullable()->constrained()->nullOnDelete();

            $table->text('description')->nullable();
            $table->boolean('is_planned')->default(false);

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['machine_id', 'start_time']);
            $table->index(['production_line_id', 'start_time']);
        });

        Schema::create('quality_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_record_id')->constrained()->cascadeOnDelete();
            $table->foreignId('machine_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();

            $table->integer('total_quantity')->default(0);
            $table->integer('good_quantity')->default(0);
            $table->integer('reject_quantity')->default(0);
            $table->integer('rework_quantity')->default(0);
            $table->integer('scrap_quantity')->default(0);

            $table->foreignId('defect_category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('defect_reason_id')->nullable()->constrained()->nullOnDelete();

            $table->dateTime('inspection_time')->nullable();
            $table->foreignId('inspector_id')->nullable()->constrained('users')->nullOnDelete();

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['production_record_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quality_records');
        Schema::dropIfExists('downtimes');
        Schema::dropIfExists('production_records');
        Schema::dropIfExists('production_runs');
        Schema::dropIfExists('production_orders');
        Schema::dropIfExists('production_plans');
    }
};
