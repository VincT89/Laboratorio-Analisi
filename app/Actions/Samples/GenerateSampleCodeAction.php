<?php

namespace App\Actions\Samples;

use App\Models\Sample;
use Illuminate\Support\Facades\DB;
use Exception;

class GenerateSampleCodeAction
{
    /**
     * Genera un codice univoco per il campione.
     * Formato: XXXX/YY (es. 0001/26)
     * Ritorna un array con: code, progressive, year.
     */
    public function execute(?int $forcedProgressive = null): array
    {
        return DB::transaction(function () use ($forcedProgressive) {
            $yearStr = now()->format('y');
            $year = (int) $yearStr;

            if ($forcedProgressive !== null) {
                // Se forzato, verifichiamo che non esista già
                $exists = Sample::where('code_year', $year)
                    ->where('code_progressive', $forcedProgressive)
                    ->exists();

                if ($exists) {
                    throw new Exception("Il progressivo {$forcedProgressive} è già in uso per l'anno {$yearStr}.");
                }

                $nextSeq = $forcedProgressive;
            } else {
                $lastSeq = Sample::where('code_year', $year)
                    ->lockForUpdate()
                    ->max('code_progressive');

                $nextSeq = ($lastSeq ?? 0) + 1;
            }

            $seqStr = str_pad($nextSeq, 4, '0', STR_PAD_LEFT);
            $code = "{$seqStr}/{$yearStr}";

            return [
                'code' => $code,
                'progressive' => $nextSeq,
                'year' => $year,
            ];
        });
    }
}
