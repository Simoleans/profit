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
        Schema::table('encabezado', function (Blueprint $table) {
            $table->string('nro_cot')->nullable();
            $table->string('nro_ped')->nullable();
            $table->string('nro_fact')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('encabezado', function (Blueprint $table) {
            $table->dropColumn('nro_cot');
            $table->dropColumn('nro_ped');
            $table->dropColumn('nro_fact');
        });
    }
};
