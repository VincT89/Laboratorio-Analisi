<?php

namespace App\Helpers;

use App\Models\SampleType;
use Illuminate\Support\Collection;

class SampleActivityHelper
{
    /** @var array<int, string> */
    protected static array $typeCache = [];
    protected static bool $isCacheLoaded = false;

    /** @var array<int, string> */
    protected static array $clientCache = [];
    protected static bool $isClientCacheLoaded = false;

    /**
     * Risolve il valore del sample_type dallo storico Spatie Activitylog.
     * Gestisce sia la vecchia colonna testuale sia la nuova colonna FK tramite una cache array
     * popolata una tantum per annullare il problema N+1 sulle Timeline.
     */
    public static function resolveSampleTypeDisplay(?string $oldValue, ?string $newValue, Collection $activities): array
    {
        self::primeCache($activities);

        $oldDisp = self::resolveValue($oldValue);
        $newDisp = self::resolveValue($newValue);

        return [$oldDisp, $newDisp];
    }

    /**
     * Risolve un singolo valore prelevato da Spatie, se fosse numerico.
     */
    public static function resolveSingleValue($val): string
    {
        // Se non è caricata la cache e abbiamo un numerico, facciamo un find diretto o usiamo la cache se c'è
        if (is_numeric($val)) {
            if (!self::$isCacheLoaded) {
                $type = SampleType::find((int)$val);
                return $type ? $type->name : "Tipo Storico / ID #{$val}";
            }
            return self::$typeCache[(int)$val] ?? "Tipo Storico / ID #{$val}";
        }

        return $val ?: 'Nessuno';
    }

    protected static function resolveValue($val): string
    {
        if (is_null($val) || $val === '') {
            return 'Nessuno';
        }

        if (is_numeric($val)) {
            return self::$typeCache[(int)$val] ?? "Tipo Storico dismesso (ID #{$val})";
        }

        // Caso stringa pura legacy
        return $val;
    }

    /**
     * Raccoglie tutti gli ID di sample_types presenti tra le property di una collection di log
     * e riempie la Cache in un'unica Query.
     */
    protected static function primeCache(Collection $activities): void
    {
        if (self::$isCacheLoaded) {
            return;
        }

        $idsToFetch = [];

        foreach ($activities as $activity) {
            $changes = $activity->changes();
            if (!$changes || !isset($changes['attributes'])) {
                continue;
            }

            if (isset($changes['attributes']['sample_type_id'])) {
                $rawNew = $changes['attributes']['sample_type_id'];
                if (is_numeric($rawNew)) {
                    $idsToFetch[] = (int)$rawNew;
                }
            }
            if (isset($changes['old']['sample_type_id'])) {
                $rawOld = $changes['old']['sample_type_id'];
                if (is_numeric($rawOld)) {
                    $idsToFetch[] = (int)$rawOld;
                }
            }
        }

        $idsToFetch = array_unique($idsToFetch);

        if (!empty($idsToFetch)) {
            $types = SampleType::whereIn('id', $idsToFetch)->get();
            foreach ($types as $type) {
                self::$typeCache[$type->id] = $type->name;
            }
        }

        self::$isCacheLoaded = true;
    }

    /**
     * Risolve il nome completo di un cliente dato l'id
     */
    public static function resolveSingleClientValue($val): string
    {
        if (is_numeric($val)) {
            if (!self::$isClientCacheLoaded) {
                $client = \App\Models\Client::find((int)$val);
                return $client ? ($client->company_name ?: $client->first_name . ' ' . $client->last_name) : "Cliente #{$val}";
            }
            return self::$clientCache[(int)$val] ?? "Cliente #{$val}";
        }

        return $val ?: 'Nessuno';
    }

    /**
     * Precarica i client necessari
     */
    public static function primeClientCache(Collection $activities): void
    {
        if (self::$isClientCacheLoaded) {
            return;
        }

        $idsToFetch = [];

        foreach ($activities as $activity) {
            $changes = $activity->changes();
            if (!$changes || !isset($changes['attributes'])) {
                continue;
            }

            if (isset($changes['attributes']['client_id']) && is_numeric($changes['attributes']['client_id'])) {
                $idsToFetch[] = (int)$changes['attributes']['client_id'];
            }
            if (isset($changes['old']['client_id']) && is_numeric($changes['old']['client_id'])) {
                $idsToFetch[] = (int)$changes['old']['client_id'];
            }
        }

        $idsToFetch = array_unique($idsToFetch);

        if (!empty($idsToFetch)) {
            $clients = \App\Models\Client::whereIn('id', $idsToFetch)->get();
            foreach ($clients as $client) {
                self::$clientCache[$client->id] = $client->company_name ?: $client->first_name . ' ' . $client->last_name;
            }
        }

        self::$isClientCacheLoaded = true;
    }

    /**
     * Forza lo svuotamento o ricaricamento (utile nei Test)
     */
    public static function flushCache(): void
    {
        self::$typeCache = [];
        self::$isCacheLoaded = false;
        self::$clientCache = [];
        self::$isClientCacheLoaded = false;
    }
}
