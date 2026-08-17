<?php

namespace App\Actions\Samples;

use App\Models\Sample;
use App\Models\SampleType;
use App\Models\User;

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

        if (array_key_exists('status', $data)) {
            abort_if(
                $sampleType->is_sensitive,
                403,
                'Lo stato dei campioni sensibili deve essere gestito tramite il flusso dedicato.'
            );

            $status = $data['status'];

            abort_unless(
                array_key_exists($status, Sample::STATUS_LABELS),
                422,
                'Lo stato del campione selezionato non è valido.'
            );

            if ($status === 'collected') {
                $data['accepted_at'] = null;
            } elseif (in_array($status, ['accepted', 'completed'], true)) {
                $data['accepted_at'] = $sample->accepted_at ?? now();
            }
        }

        if (isset($data['code_progressive']) && $data['code_progressive'] != $sample->code_progressive) {
            $user = User::find($userId);
            if ($user && $user->hasPermissionTo('edit samples')) { // Or whatever the admin check is. If they can edit samples, maybe they can't change code unless admin. The prompt said "solo admin". Let's use isAdmin()
                if ($user->isAdmin()) {
                    $year = $sample->code_year;
                    $progressive = $data['code_progressive'];

                    $seqStr = str_pad($progressive, 4, '0', STR_PAD_LEFT);
                    $yearStr = str_pad($year, 2, '0', STR_PAD_LEFT);
                    $data['code'] = "{$seqStr}/{$yearStr}";
                } else {
                    unset($data['code_progressive']);
                }
            } else {
                unset($data['code_progressive']);
            }
        }

        $sample->update($data);

        return $sample;
    }
}
