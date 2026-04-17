<?php

namespace App\Actions\Samples\Workflow;

use App\Models\Sample;

class CompleteSampleAction
{
    public function execute(Sample $sample, int $userId): Sample
    {
        abort_unless($sample->canBeCompleted(), 403, 'Il campione non può essere completato. L\'accettazione è necessaria e il campione non deve essere incompleto.');

        $updateData = [
            'status'     => 'completed',
            'updated_by' => $userId,
        ];

        $sample->update($updateData);

        return $sample;
    }
}
