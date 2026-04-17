<?php

namespace App\Actions\Samples\Workflow;

use App\Models\Sample;
use Illuminate\Support\Facades\DB;

class RestoreSampleAction
{
    public function execute(Sample $sample, int $userId): Sample
    {
        DB::transaction(function () use ($sample, $userId) {
            $sample->update([
                'archived'    => false,
                'archived_at' => null,
                'archived_by' => null,
                'updated_by'  => $userId,
            ]);

            $sample->files()->update([
                'archived'    => false,
                'archived_at' => null,
                'archived_by' => null,
            ]);
        });

        return $sample;
    }
}
