<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('product_cycle_times', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('production_line_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('machine_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('ideal_cycle_time', 10, 4)->comment('Seconds per unit');
            $table->string('unit', 20)->default('sec/unit');
            $table->string('revision', 20)->default('Rev 1');
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->string('status', 20)->default('ACTIVE')->comment('ACTIVE, HISTORICAL, INACTIVE');
            $table->text('reason')->nullable()->comment('Reason for change / Kaizen note');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['product_id', 'effective_from', 'effective_to']);
            $table->index(['product_id', 'status']);
        });

        // Seed initial Rev 1 cycle times from existing products table
        if (Schema::hasTable('products')) {
            $products = DB::table('products')->whereNull('deleted_at')->get();
            $now = now();
            foreach ($products as $product) {
                $ict = (float) ($product->ideal_cycle_time ?? 9.5);
                if ($ict <= 0) $ict = 9.5;

                DB::table('product_cycle_times')->insert([
                    'product_id' => $product->id,
                    'production_line_id' => $product->production_line_id ?? null,
                    'machine_id' => null,
                    'ideal_cycle_time' => $ict,
                    'unit' => 'sec/unit',
                    'revision' => 'Rev 1',
                    'effective_from' => '2020-01-01',
                    'effective_to' => null,
                    'status' => 'ACTIVE',
                    'reason' => 'Standar awal desain lini produksi (Initial baseline)',
                    'created_by' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_cycle_times');
    }
};
