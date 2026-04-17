<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\User;

class ClientPolicy
{
    /**
     * L'admin bypassa tutte le policy.
     */
    public function before(User $user, string $ability): bool|null
    {
        if ($user->isAdmin()) {
            return true;
        }

        return null;
    }

    /**
     * Lo staff può vedere i clienti attivi.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view clients');
    }

    /**
     * Lo staff può vedere il dettaglio di un cliente.
     */
    public function view(User $user, Client $client): bool
    {
        return $user->hasPermissionTo('view clients');
    }

    /**
     * Lo staff può creare nuovi clienti.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create clients');
    }

    /**
     * Lo staff può modificare un cliente non archiviato.
     */
    public function update(User $user, Client $client): bool
    {
        if ($client->archived) {
            return false;
        }

        return $user->hasPermissionTo('edit clients');
    }

    /**
     * Solo Admin può archiviare.
     * L'archiviazione comporta un effetto a cascata su campioni e file.
     */
    public function archive(User $user, Client $client): bool
    {
        return false;
    }

    /**
     * Solo Admin può ripristinare un cliente e la sua cascata di dati.
     */
    public function restore(User $user, Client $client): bool
    {
        return false;
    }

    /**
     * Solo l'admin può eliminare definitivamente un cliente.
     * Gestito dal metodo before(), mai raggiunto dallo staff.
     */
    public function delete(User $user, Client $client): bool
    {
        return false;
    }
}