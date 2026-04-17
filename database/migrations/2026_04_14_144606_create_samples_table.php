<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('samples', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();

            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();

            // Dati prelievo
            $table->date('collected_at');
            $table->string('sample_type');
            $table->string('collection_site');
            $table->string('collected_by'); // nome libero, non FK utente

            // Dati accettazione
            $table->date('accepted_at')->nullable();

            // Stato
            $table->enum('status', ['collected', 'accepted', 'completed'])->default('collected');

            // Note
            $table->text('notes')->nullable();

            // Archiviazione
            $table->boolean('archived')->default(false);
            $table->timestamp('archived_at')->nullable();
            $table->foreignId('archived_by')->nullable()->constrained('users')->nullOnDelete();

            // Tracciabilità
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('samples');
    }
};