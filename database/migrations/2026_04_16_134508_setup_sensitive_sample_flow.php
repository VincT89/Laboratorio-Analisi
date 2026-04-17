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
        Schema::table('sample_types', function (Blueprint $table) {
            $table->boolean('is_sensitive')->default(false)->after('is_active');
        });

        Schema::table('samples', function (Blueprint $table) {
            $table->foreignId('client_id')->nullable()->change();
            $table->string('collection_site')->nullable()->change();
            $table->string('collected_by')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('samples', function (Blueprint $table) {
            $table->foreignId('client_id')->nullable(false)->change();
            $table->string('collection_site')->nullable(false)->change();
            $table->string('collected_by')->nullable(false)->change();
        });

        Schema::table('sample_types', function (Blueprint $table) {
            $table->dropColumn('is_sensitive');
        });
    }
};
