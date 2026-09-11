<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations to safely drop unused legacy scaffolding tables.
     */
    public function up(): void
    {
        // 1. Remove foreign key and column production_order_id from production_records
        if (Schema::hasTable('production_records')) {
            Schema::table('production_records', function (Blueprint $table) {
                if (Schema::hasColumn('production_records', 'production_order_id')) {
                    // Drop foreign key if exists
                    try {
                        $table->dropForeign(['production_order_id']);
                    } catch (\Exception $e) {
                        // ignore if already dropped
                    }
                    $table->dropColumn('production_order_id');
                }
            });
        }

        // 2. Drop unused pivot and legacy tables in correct dependency order
        Schema::dropIfExists('machine_products');
        Schema::dropIfExists('production_runs');
        Schema::dropIfExists('production_orders');
        Schema::dropIfExists('production_plans');
        Schema::dropIfExists('processes');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate processes
        Schema::create('processes', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Recreate production_plans
        Schema::create('production_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_line_id')->constrained()->cascadeOnDelete();
            $table->string('plan_number', 50)->unique();
            $table->date('start_date');
            $table->date('end_date');
            $table->string('status', 30)->default('SCHEDULED');
            $table->timestamps();
        });

        // Recreate production_orders
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

        // Recreate production_runs
        Schema::create('production_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('production_line_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shift_id')->constrained()->cascadeOnDelete();
            $table->date('run_date');
            $table->string('status', 30)->default('RUNNING');
            $table->timestamps();
        });

        // Recreate machine_products
        Schema::create('machine_products', function (Blueprint $table) {
            $table->foreignId('machine_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('process_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('specific_ideal_cycle_time', 10, 4)->nullable();
            $table->timestamps();
            $table->primary(['machine_id', 'product_id']);
        });

        if (Schema::hasTable('production_records')) {
            Schema::table('production_records', function (Blueprint $table) {
                if (!Schema::hasColumn('production_records', 'production_order_id')) {
                    $table->foreignId('production_order_id')->nullable()->after('id')->constrained()->nullOnDelete();
                }
            });
        }
    }
};
