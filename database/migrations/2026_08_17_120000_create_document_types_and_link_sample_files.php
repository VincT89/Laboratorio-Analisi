<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('code', 30)->unique();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        $now = now();
        $defaults = [
            ['name' => 'Referto / Rapporto di Prova', 'code' => 'report', 'sort_order' => 10],
            ['name' => 'Allegato Generico', 'code' => 'attachment', 'sort_order' => 20],
            ['name' => 'Certificato', 'code' => 'prescription', 'sort_order' => 30],
            ['name' => 'Referto Revisionato', 'code' => 'revised_report', 'sort_order' => 40],
        ];

        DB::table('document_types')->insert(array_map(
            fn (array $type) => [
                ...$type,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            $defaults
        ));

        Schema::table('sample_files', function (Blueprint $table) {
            $table->foreignId('document_type_id')
                ->nullable()
                ->after('type')
                ->constrained('document_types')
                ->restrictOnDelete();
        });

        foreach ($defaults as $type) {
            $documentTypeId = DB::table('document_types')
                ->where('code', $type['code'])
                ->value('id');

            DB::table('sample_files')
                ->where('type', $type['code'])
                ->update(['document_type_id' => $documentTypeId]);
        }

        $legacyCodes = DB::table('sample_files')
            ->whereNull('document_type_id')
            ->whereNotNull('type')
            ->distinct()
            ->pluck('type');

        foreach ($legacyCodes as $legacyCode) {
            $legacyCode = (string) $legacyCode;

            if ($legacyCode === '') {
                continue;
            }

            $name = Str::headline($legacyCode);
            $candidate = $name;
            $counter = 1;

            while (DB::table('document_types')->where('name', $candidate)->exists()) {
                $candidate = "{$name} storico {$counter}";
                $counter++;
            }

            $documentTypeId = DB::table('document_types')->insertGetId([
                'name' => $candidate,
                'code' => mb_substr($legacyCode, 0, 30),
                'is_active' => false,
                'sort_order' => 9999,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('sample_files')
                ->where('type', $legacyCode)
                ->update(['document_type_id' => $documentTypeId]);
        }
    }

    public function down(): void
    {
        Schema::table('sample_files', function (Blueprint $table) {
            $table->dropConstrainedForeignId('document_type_id');
        });

        Schema::dropIfExists('document_types');
    }
};
