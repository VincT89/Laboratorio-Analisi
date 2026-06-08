<?php

namespace App\Policies;

use App\Models\Sample;
use App\Models\User;

class SamplePolicy
{
    public function before(User $user, string $ability, ...$args): bool|null
    {
        if ($user->isAdmin()) {
            return true;
        }

        $sample = $args[0] ?? null;
        if ($sample instanceof Sample && $sample->isSensitive() && $user->hasRole('staff')) {
            return false;
        }

        return null;
    }

    /**
     * Lo staff può vedere la lista dei campioni.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view samples');
    }

    /**
     * Lo staff può vedere il dettaglio di un campione.
     */
    public function view(User $user, Sample $sample): bool
    {
        return $user->hasPermissionTo('view samples');
    }

    /**
     * Lo staff può creare nuovi campioni.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create samples');
    }

    /**
     * Lo staff può modificare un campione anche se completato,
     * ma non se archiviato.
     */
    public function update(User $user, Sample $sample): bool
    {
        if ($sample->archived) {
            return false;
        }

        return $user->hasPermissionTo('edit samples');
    }

    /**
     * Lo staff può inserire la data di accettazione
     * solo se il campione è nello stato 'collected'.
     */
    public function accept(User $user, Sample $sample): bool
    {
        if ($sample->archived || $sample->status !== 'collected') {
            return false;
        }

        return $user->hasPermissionTo('edit samples');
    }

    /**
     * Lo staff può completare un campione
     * solo se è nello stato 'accepted'.
     */
    public function complete(User $user, Sample $sample): bool
    {
        if ($sample->archived || $sample->status !== 'accepted') {
            return false;
        }

        return $user->hasPermissionTo('edit samples');
    }

    /**
     * Lo staff può rifiutare un campione.
     */
    public function reject(User $user, Sample $sample): bool
    {
        return $user->hasPermissionTo('edit samples')
            && !$sample->archived
            && !in_array($sample->status, ['completed', 'rejected'], true);
    }

    /**
     * Solo Admin può archiviare il campione (rimozione logica su file in cascata).
     */
    public function archive(User $user, Sample $sample): bool
    {
        return false;
    }

    /**
     * Solo Admin può ripristinare un campione e i suoi file associati.
     */
    public function restore(User $user, Sample $sample): bool
    {
        return false;
    }

    /**
     * Solo l'admin può eliminare definitivamente un campione.
     * Gestito dal metodo before(), mai raggiunto dallo staff.
     */
    public function delete(User $user, Sample $sample): bool
    {
        return false;
    }
}