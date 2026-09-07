<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plant_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name', 50); // e.g. Shift 1, Shift 2, Shift 3
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('break_duration_minutes')->default(60);
            $table->timestamps();
        });

        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->string('nik', 30)->unique();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('operators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('badge_number', 30)->unique();
            $table->string('skill_level', 30)->default('INTERMEDIATE');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operators');
        Schema::dropIfExists('employees');
        Schema::dropIfExists('shifts');
    }
};
