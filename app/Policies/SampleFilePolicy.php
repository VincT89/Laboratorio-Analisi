<?php

namespace App\Policies;

use App\Models\SampleFile;
use App\Models\User;

class SampleFilePolicy
{
    public function before(User $user, string $ability, ...$args): bool|null
    {
        if ($user->isAdmin()) {
            return true;
        }
        
        $sampleFile = $args[0] ?? null;
        if ($sampleFile instanceof SampleFile && $sampleFile->sample?->isSensitive() && $user->hasRole('staff')) {
            return false;
        }

        return null;
    }

    /**
     * Lo staff può vedere i file di un campione.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view files');
    }

    /**
     * Lo staff può vedere il dettaglio di un file.
     */
    public function view(User $user, SampleFile $sampleFile): bool
    {
        return $user->hasPermissionTo('view files');
    }

    /**
     * Lo staff può caricare file su un campione non archiviato.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('upload files');
    }

    /**
     * Lo staff può scaricare un file non archiviato di un campione non archiviato.
     */
    public function download(User $user, SampleFile $sampleFile): bool
    {
        if (!$sampleFile->isDownloadable()) {
            return false;
        }

        return $user->hasPermissionTo('view files');
    }

    /**
     * Lo staff può archiviare un file.
     */
    public function archive(User $user, SampleFile $sampleFile): bool
    {
        return false;
    }

    /**
     * Solo l'admin può eliminare definitivamente un file.
     * Gestito dal metodo before(), mai raggiunto dallo staff.
     */
    public function delete(User $user, SampleFile $sampleFile): bool
    {
        return false;
    }
}