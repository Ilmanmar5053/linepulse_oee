<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ng_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_record_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('machine_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shift_id')->constrained()->cascadeOnDelete();
            $table->date('production_date');

            // Total NG Target from Production Record
            $table->integer('total_ng_target')->default(0);

            // Mandatory OEE NG Components (Assy, Rod, Cap)
            $table->integer('ng_assy')->default(0);
            $table->integer('ng_rod')->default(0);
            $table->integer('ng_cap')->default(0);
            $table->integer('total_ng_oee')->default(0);

            // Optional Non-OEE Supplementary Fasteners/Parts (Bolt, Bush, Nut, Pin)
            $table->integer('ng_bolt')->default(0);
            $table->integer('ng_bush')->default(0);
            $table->integer('ng_nut')->default(0);
            $table->integer('ng_pin')->default(0);
            $table->integer('total_ng_non_oee')->default(0);

            // Defect Reason & Quality Analysis Context
            $table->foreignId('defect_category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('defect_reason_id')->nullable()->constrained()->nullOnDelete();
            $table->string('defect_symptom')->nullable();
            $table->string('action_taken')->default('SCRAP'); // SCRAP, REWORK, HOLD/QUARANTINE
            $table->string('inspector_name')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['production_date', 'shift_id']);
            $table->index(['machine_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ng_records');
    }
};
