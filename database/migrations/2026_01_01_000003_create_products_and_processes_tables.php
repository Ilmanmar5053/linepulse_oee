<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('sku', 50)->unique();
            $table->string('name');
            $table->string('unit_of_measure', 20)->default('PCS');
            $table->decimal('ideal_cycle_time', 10, 4)->comment('Seconds per unit');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('processes', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('machine_products', function (Blueprint $table) {
            $table->foreignId('machine_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('process_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('specific_ideal_cycle_time', 10, 4)->nullable()->comment('Seconds per unit on this machine');
            $table->timestamps();
            $table->primary(['machine_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('machine_products');
        Schema::dropIfExists('processes');
        Schema::dropIfExists('products');
        Schema::dropIfExists('product_categories');
    }
};
