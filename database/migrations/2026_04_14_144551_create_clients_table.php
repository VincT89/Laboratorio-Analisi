<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('company_name');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->enum('type', ['company', 'individual'])->default('company');

            // Contatti
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('pec')->nullable();

            // Indirizzo
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('province', 5)->nullable();
            $table->string('postal_code', 10)->nullable();
            $table->string('country')->default('Italia');

            // Fatturazione
            $table->string('tax_code')->nullable();
            $table->string('vat_number')->nullable();
            $table->string('sdi_code')->nullable();

            // Note
            $table->text('notes')->nullable();

            // Archiviazione
            $table->boolean('archived')->default(false);
            $table->timestamp('archived_at')->nullable();
            $table->foreignId('archived_by')->nullable()->constrained('users')->nullOnDelete();

            // Tracciabilità
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};