<?php

namespace App\Queries\Samples;

use App\Models\Sample;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class ActiveSamplesIndexQuery
{
    public function __construct(
        private AppliesStaffSafeSearch $searchApplier
    ) {}

    /**
     * Esegue la query dei campioni attivi applicando i filtri di input.
     *
     * @param array $filters (es. search, status, sample_type_id, collected_from...)
     * @param User $user
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function paginate(array $filters, User $user, int $perPage = 20): LengthAwarePaginator
    {
        $query = Sample::active()
            ->with(['client', 'sampleType'])
            ->withCount(['files' => fn($q) => $q->active()]);

        if (!empty($filters['search'])) {
            $isStaff = $user->hasRole('staff');
            $this->searchApplier->apply($query, $filters['search'], $isStaff);
        }

        if (!empty($filters['status'])) {
            if ($filters['status'] === 'incomplete') {
                // Solo l'admin può vedere i campioni "sensibili incompleti"
                abort_if(! $user->isAdmin(), 403, 'Accesso non autorizzato alla coda dei pre-registrati.');
                $query->sensitiveIncomplete();
            } else {
                $query->where('status', $filters['status']);
            }
        }

        if (!empty($filters['sample_type_id'])) {
            $query->where('sample_type_id', $filters['sample_type_id']);
        }

        if (!empty($filters['collected_from'])) {
            $query->where('collected_at', '>=', \Carbon\Carbon::parse($filters['collected_from'])->startOfDay());
        }
        if (!empty($filters['collected_to'])) {
            $query->where('collected_at', '<=', \Carbon\Carbon::parse($filters['collected_to'])->endOfDay());
        }
        
        if (!empty($filters['accepted_from'])) {
            $query->where('accepted_at', '>=', \Carbon\Carbon::parse($filters['accepted_from'])->startOfDay());
        }
        if (!empty($filters['accepted_to'])) {
            $query->where('accepted_at', '<=', \Carbon\Carbon::parse($filters['accepted_to'])->endOfDay());
        }

        $this->applySorting($query, $filters);

        $paginator = $query->paginate($perPage)
            ->withQueryString();

        $isAdmin = $user->isAdmin();
        $paginator->through(fn($sample) => new \App\ViewModels\SampleRowViewModel($sample, $isAdmin));

        return $paginator;
    }

    /**
     * Applica esclusivamente gli ordinamenti ammessi dalla lista campioni.
     */
    private function applySorting(Builder $query, array $filters): void
    {
        $sort = in_array($filters['sort'] ?? null, ['collected_at', 'acceptance_number'], true)
            ? $filters['sort']
            : 'collected_at';

        $direction = in_array($filters['direction'] ?? null, ['asc', 'desc'], true)
            ? $filters['direction']
            : 'desc';

        if ($sort === 'acceptance_number') {
            $query
                // I vecchi record privi di progressivo restano in fondo alla lista.
                ->orderByRaw('code_year IS NULL')
                ->orderBy('code_year', $direction)
                ->orderBy('code_progressive', $direction)
                ->orderBy('code', $direction);

            return;
        }

        $query
            ->orderBy('collected_at', $direction)
            ->orderBy('id', $direction);
    }
}
