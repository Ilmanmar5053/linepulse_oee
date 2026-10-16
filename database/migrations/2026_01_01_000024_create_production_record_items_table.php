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
        Schema::create('production_record_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_record_id')->constrained('production_records')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->decimal('ideal_cycle_time', 8, 2)->default(9.50);
            $table->integer('target_quantity')->default(0);
            $table->integer('good_quantity')->default(0);
            $table->integer('reject_quantity')->default(0);
            $table->integer('scrap_quantity')->default(0);
            $table->integer('total_quantity')->default(0);
            $table->string('remarks', 255)->nullable();
            $table->timestamps();

            $table->index(['production_record_id', 'product_id'], 'prod_rec_item_prod_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_record_items');
    }
};
