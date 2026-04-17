<?php

namespace App\Queries\Samples;

use App\Models\Sample;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class ArchivedSamplesIndexQuery
{
    public function __construct(
        private AppliesStaffSafeSearch $searchApplier
    ) {}

    public function paginate(array $filters, User $user, int $perPage = 20): LengthAwarePaginator
    {
        $query = Sample::archived()
            ->with(['client'])
            ->withCount(['files' => fn($q) => $q->active()]);

        if (!empty($filters['search'])) {
            $isStaff = $user->hasRole('staff');
            $this->searchApplier->apply($query, $filters['search'], $isStaff);
        }

        if (!empty($filters['sample_type_id'])) {
            $query->where('sample_type_id', $filters['sample_type_id']);
        }

        $paginator = $query->orderByDesc('archived_at')
            ->paginate($perPage)
            ->withQueryString();

        $isAdmin = $user->isAdmin();
        $paginator->through(fn($sample) => new \App\ViewModels\SampleRowViewModel($sample, $isAdmin));

        return $paginator;
    }
}
