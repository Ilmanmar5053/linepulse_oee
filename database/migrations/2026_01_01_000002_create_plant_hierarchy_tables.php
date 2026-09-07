<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plants', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name');
            $table->text('address')->nullable();
            $table->string('timezone', 50)->default('Asia/Jakarta');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('areas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plant_id')->constrained()->cascadeOnDelete();
            $table->string('code', 30);
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['plant_id', 'code']);
        });

        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plant_id')->constrained()->cascadeOnDelete();
            $table->string('code', 30);
            $table->string('name');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('production_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('area_id')->constrained()->cascadeOnDelete();
            $table->string('code', 30)->unique();
            $table->string('name');
            $table->decimal('target_oee', 5, 2)->default(85.00);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('work_centers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_line_id')->constrained()->cascadeOnDelete();
            $table->string('code', 30);
            $table->string('name');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('machine_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('machines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_center_id')->constrained()->cascadeOnDelete();
            $table->foreignId('machine_type_id')->nullable()->constrained()->nullOnDelete();
            $table->string('code', 30)->unique();
            $table->string('name');
            $table->string('serial_number')->nullable();
            $table->date('installation_date')->nullable();
            $table->enum('status', [
                'RUNNING',
                'STOPPED',
                'IDLE',
                'BREAKDOWN',
                'CHANGEOVER',
                'MAINTENANCE',
                'OFFLINE'
            ])->default('STOPPED');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['work_center_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('machines');
        Schema::dropIfExists('machine_types');
        Schema::dropIfExists('work_centers');
        Schema::dropIfExists('production_lines');
        Schema::dropIfExists('departments');
        Schema::dropIfExists('areas');
        Schema::dropIfExists('plants');
    }
};
