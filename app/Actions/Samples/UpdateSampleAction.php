<?php

namespace App\Actions\Samples;

use App\Models\Sample;
use App\Models\SampleType;

class UpdateSampleAction
{
    public function execute(Sample $sample, array $data, int $userId): Sample
    {
        $data['updated_by'] = $userId;

        $sampleTypeId = $data['sample_type_id'] ?? $sample->sample_type_id;
        $sampleType = SampleType::findOrFail($sampleTypeId);
        
        if ($sample->sample_type_id !== $sampleTypeId) {
            $oldType = SampleType::find($sample->sample_type_id);
            if ($oldType && $oldType->is_sensitive !== $sampleType->is_sensitive) {
                // Opzione A discussa: Blocchi il cambio di dominibilità.
                abort(403, 'Non è consentito variare la classe di sensibilità di un campione esistente.');
            }
        }
        
        $data['sample_type'] = $sampleType->name; // Fallback text column

        $sample->update($data);

        return $sample;
    }
}
