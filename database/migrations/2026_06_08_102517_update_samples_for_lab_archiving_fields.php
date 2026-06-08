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
        Schema::table('samples', function (Blueprint $table) {
            $table->string('lab_archived_by_name')->nullable();
            $table->foreignId('container_type_id')->nullable()->constrained('container_types')->nullOnDelete();
            $table->string('conservation_status')->nullable();
            $table->string('sample_quantity')->nullable();
            $table->unsignedInteger('code_progressive')->nullable();
            $table->unsignedSmallInteger('code_year')->nullable();

            $table->unique(['code_year', 'code_progressive']);
        });

        // Update enum for MySQL
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE samples MODIFY COLUMN status ENUM('collected', 'accepted', 'completed', 'rejected') DEFAULT 'collected'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert enum (if the status was rejected, it might fail, but let's assume we just revert structure)
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE samples MODIFY COLUMN status ENUM('collected', 'accepted', 'completed') DEFAULT 'collected'");
        }

        Schema::table('samples', function (Blueprint $table) {
            $table->dropUnique('samples_code_year_code_progressive_unique');
            $table->dropForeign(['container_type_id']);
            
            $table->dropColumn([
                'lab_archived_by_name',
                'container_type_id',
                'conservation_status',
                'sample_quantity',
                'code_progressive',
                'code_year'
            ]);
        });
    }
};
