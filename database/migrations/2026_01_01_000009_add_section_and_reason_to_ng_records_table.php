<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ng_records', function (Blueprint $table) {
            // Rincian Bagian NG & Penyebab/Remark untuk 3 Komponen Wajib OEE
            $table->string('assy_section')->nullable()->after('ng_assy');
            $table->string('assy_reason')->nullable()->after('assy_section');

            $table->string('rod_section')->nullable()->after('ng_rod');
            $table->string('rod_reason')->nullable()->after('rod_section');

            $table->string('cap_section')->nullable()->after('ng_cap');
            $table->string('cap_reason')->nullable()->after('cap_section');
        });
    }

    public function down(): void
    {
        Schema::table('ng_records', function (Blueprint $table) {
            $table->dropColumn([
                'assy_section',
                'assy_reason',
                'rod_section',
                'rod_reason',
                'cap_section',
                'cap_reason',
            ]);
        });
    }
};
