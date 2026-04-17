<?php

return [
    /*
    Qui vengono gestite le funzionalita attivabili e disattivabili globalmente
    per l'intera applicazione. E' possibile modificare i valori direttamente
    qui oppure tramite le variabili d'ambiente (nel file .env).
    */

    'registration_enabled' => env('REGISTRATION_ENABLED', false),
];
