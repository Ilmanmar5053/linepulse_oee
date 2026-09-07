<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('groups')) {
            Schema::create('groups', function (Blueprint $table) {
                $table->id();
                $table->string('team', 30);
                $table->string('leader_name');
                $table->foreignId('production_line_id')->constrained('production_lines')->cascadeOnDelete();
                $table->timestamps();
                $table->unique(['production_line_id', 'team']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('groups');
    }
};
