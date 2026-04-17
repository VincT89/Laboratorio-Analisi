<?php

namespace App\Actions\Samples\Workflow;

use App\Models\Sample;
use Illuminate\Support\Facades\DB;

class ArchiveSampleAction
{
    public function execute(Sample $sample, int $userId): Sample
    {
        DB::transaction(function () use ($sample, $userId) {
            $sample->files()->update([
                'archived'    => true,
                'archived_at' => now(),
                'archived_by' => $userId,
            ]);

            $sample->update([
                'archived'    => true,
                'archived_at' => now(),
                'archived_by' => $userId,
            ]);
        });

        return $sample;
    }
}
