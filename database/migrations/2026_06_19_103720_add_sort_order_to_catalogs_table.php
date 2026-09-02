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
        Schema::table('measurement_units', function (Blueprint $table) {
            $table->integer('sort_order')->default(0)->after('is_active');
        });
        
        Schema::table('conservation_statuses', function (Blueprint $table) {
            $table->integer('sort_order')->default(0)->after('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('measurement_units', function (Blueprint $table) {
            $table->dropColumn('sort_order');
        });
        
        Schema::table('conservation_statuses', function (Blueprint $table) {
            $table->dropColumn('sort_order');
        });
    }
};
