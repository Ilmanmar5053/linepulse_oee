<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('downtime_categories', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name');
            $table->boolean('is_planned')->default(false);
            $table->timestamps();
        });

        Schema::create('downtime_reasons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('downtime_category_id')->constrained()->cascadeOnDelete();
            $table->enum('six_big_loss_category', [
                'EQUIPMENT_FAILURE',
                'SETUP_ADJUSTMENT',
                'IDLING_MINOR_STOP',
                'REDUCED_SPEED',
                'PROCESS_DEFECTS',
                'REDUCED_YIELD'
            ])->default('EQUIPMENT_FAILURE');
            $table->string('code', 30)->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('defect_categories', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('defect_reasons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('defect_category_id')->constrained()->cascadeOnDelete();
            $table->string('code', 30)->unique();
            $table->string('name');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('defect_reasons');
        Schema::dropIfExists('defect_categories');
        Schema::dropIfExists('downtime_reasons');
        Schema::dropIfExists('downtime_categories');
    }
};
