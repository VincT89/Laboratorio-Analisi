<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sample_files', function (Blueprint $table) {
            $table->id();

            $table->foreignId('sample_id')->constrained('samples')->cascadeOnDelete();

            // Info file
            $table->string('original_name');
            $table->string('path');
            $table->string('mime_type')->nullable();
            $table->string('extension', 10)->nullable();
            $table->unsignedBigInteger('size')->nullable(); // in bytes

            // Note opzionali sul file
            $table->string('description')->nullable();

            // Archiviazione
            $table->boolean('archived')->default(false);
            $table->timestamp('archived_at')->nullable();
            $table->foreignId('archived_by')->nullable()->constrained('users')->nullOnDelete();

            // Tracciabilità
            $table->foreignId('uploaded_by')->constrained('users')->restrictOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sample_files');
    }
};