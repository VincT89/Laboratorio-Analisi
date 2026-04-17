<?php

namespace App\Actions\Samples\Workflow;

use App\Models\Sample;

class AcceptSampleAction
{
    public function execute(Sample $sample, int $userId): Sample
    {
        abort_unless($sample->canBeAccepted(), 403, 'Il campione non può essere accettato in questo stato (verifica anche se è un sensibile incompleto).');

        $sample->update([
            'accepted_at' => now(),
            'status'      => 'accepted',
            'updated_by'  => $userId,
        ]);

        return $sample;
    }
}
