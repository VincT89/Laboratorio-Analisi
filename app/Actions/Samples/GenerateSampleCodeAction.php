<?php

namespace App\Actions\Samples;

use App\Models\Sample;
use Illuminate\Support\Facades\DB;

class GenerateSampleCodeAction
{
    /**
     * Genera un codice univoco per il campione.
     * Formato: LAB-YYYY-XXXXX (es. LAB-2026-00001)
     */
    public function execute(): string
    {
        return DB::transaction(function () {
            $year   = now()->year;
            $prefix = "LAB-{$year}-";

            $lastCode = Sample::where('code', 'like', "{$prefix}%")
                ->lockForUpdate()
                ->orderByDesc('code')
                ->value('code');

            $lastSeq = 0;

            if ($lastCode && preg_match('/^LAB-\d{4}-(\d{5})$/', $lastCode, $matches)) {
                $lastSeq = (int) $matches[1];
            }

            $seq = str_pad($lastSeq + 1, 5, '0', STR_PAD_LEFT);

            return "{$prefix}{$seq}";
        });
    }
}
