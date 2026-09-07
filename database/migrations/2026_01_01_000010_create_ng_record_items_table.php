<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ng_record_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ng_record_id')->constrained('ng_records')->cascadeOnDelete();
            $table->string('component_type', 20); // ASSY, ROD, CAP
            $table->integer('quantity')->default(1);
            $table->string('section', 100)->nullable(); // Bagian NG
            $table->string('reason', 255)->nullable();  // Penyebab / Remark
            $table->timestamps();
        });

        Schema::table('ng_records', function (Blueprint $table) {
            $table->json('items_breakdown')->nullable()->after('cap_reason');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ng_record_items');
        Schema::table('ng_records', function (Blueprint $table) {
            $table->dropColumn('items_breakdown');
        });
    }
};
