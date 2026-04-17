<?php

namespace App\Queries\Samples;

use Illuminate\Database\Eloquent\Builder;

class AppliesStaffSafeSearch
{
    /**
     * Applica le regole di ricerca sicure per lo Staff (mascherando i risultati sensibili).
     *
     * @param Builder $query
     * @param string $search
     * @param bool $isStaff
     * @return Builder
     */
    public function apply(Builder $query, string $search, bool $isStaff): Builder
    {
        if ($isStaff) {
            // Staff search logic: cannot find sensitive samples via client name
            return $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere(function ($subQ) use ($search) {
                        $subQ->whereDoesntHave('sampleType', fn($t) => $t->where('is_sensitive', true))
                             ->whereHas('client', function ($q2) use ($search) {
                                 $q2->where('company_name', 'like', "%{$search}%")
                                    ->orWhere('first_name', 'like', "%{$search}%")
                                    ->orWhere('last_name', 'like', "%{$search}%");
                             });
                    });
            });
        }

        // Admin search logic: full access
        return $query->where(function ($q) use ($search) {
            $q->where('code', 'like', "%{$search}%")
                ->orWhereHas('client', function ($q2) use ($search) {
                    $q2->where('company_name', 'like', "%{$search}%")
                        ->orWhere('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%");
                });
        });
    }
}
