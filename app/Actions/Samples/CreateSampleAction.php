<?php

namespace App\Actions\Samples;

use App\Models\Sample;
use App\Models\SampleType;
use Illuminate\Support\Facades\DB;

class CreateSampleAction
{
    public function __construct(
        private GenerateSampleCodeAction $generateCodeAction
    ) {}

    /**
     * Crea un nuovo campione applicando la logica di dominio.
     * La source of truth per i sensibili è SampleType::is_sensitive, non i flag della request.
     */
    public function execute(array $data, int $userId): Sample
    {
        return DB::transaction(function () use ($data, $userId) {
            $data['created_by'] = $userId;
            
            $generated = $this->generateCodeAction->execute($data['code_progressive'] ?? null);
            $data['code'] = $generated['code'];
            $data['code_progressive'] = $generated['progressive'];
            $data['code_year'] = $generated['year'];

            $sampleType = SampleType::findOrFail($data['sample_type_id']);
            $data['sample_type'] = $sampleType->name; // Fallback text column

            if ($sampleType->is_sensitive) {
                // Preregistrazione Tecnica Anonima: dominio sensibile
                // Si accetta il collected_by/collection_site (necessario)
                // Ma non il cliente o le note preesistenti
                $data['client_id'] = null;
                $data['notes'] = null;
                $data['status'] = 'collected';
            }

            return Sample::create($data);
        });
    }
}
