<?php

namespace App\Actions\Samples\Workflow;

use App\Models\Sample;
use InvalidArgumentException;

class RejectSampleAction
{
    public function execute(Sample $sample, int $userId): Sample
    {
        if ($sample->status === 'completed') {
            throw new InvalidArgumentException("Non è possibile rifiutare un campione già completato.");
        }

        if ($sample->status === 'rejected') {
            throw new InvalidArgumentException("Il campione è già rifiutato.");
        }

        if ($sample->archived) {
            throw new InvalidArgumentException("Non è possibile rifiutare un campione archiviato.");
        }

        $sample->status = 'rejected';
        $sample->save();

        $user = \App\Models\User::findOrFail($userId);

        activity('samples')
            ->performedOn($sample)
            ->causedBy($user)
            ->log('rejected');

        return $sample;
    }
}
