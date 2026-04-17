<?php

namespace App\ViewModels;

use App\Models\Sample;
use Illuminate\Support\Facades\Auth;

class SampleRowViewModel
{
    public function __construct(
        public Sample $sample,
        private bool $isAdmin
    ) {}

    public static function from(Sample $sample): self
    {
        return new self($sample, Auth::user()->isAdmin());
    }

    /**
     * Consente l'accesso trasparente agli attributi sottostanti del Model Eloquent.
     */
    public function __get(string $property)
    {
        return $this->sample->{$property};
    }

    /**
     * Necessario per supportare data_get() e contains() sulle Collection di Laravel.
     */
    public function __isset(string $property): bool
    {
        return isset($this->sample->{$property});
    }

    /**
     * Determina se i dati sensibili del campione corrente 
     * devono essere mascherati.
     */
    public function isMasked(): bool
    {
        return $this->sample->isSensitive() && !$this->isAdmin;
    }

    /**
     * Il nome azienda o persona fisica, oppure il placeholder se mascherato.
     */
    public function clientName(): string
    {
        if ($this->isMasked()) {
            return '******';
        }

        if ($this->sample->client) {
            return $this->sample->client->company_name ?: 
                   ($this->sample->client->first_name . ' ' . $this->sample->client->last_name);
        }

        return '— Preregistrato —';
    }

    /**
     * Restituisce la tipologia azienda/privato (null se mascherato o assente).
     */
    public function clientType(): ?string
    {
        if ($this->isMasked() || !$this->sample->client) {
            return null;
        }

        return $this->sample->client->type === 'company' ? 'Azienda' : 'Privato';
    }

    /**
     * Restituisce il nome del tipo campione originario o asteriscato.
     */
    public function sampleTypeName(): string
    {
        if ($this->isMasked()) {
            return '******';
        }

        return $this->sample->sampleType ? $this->sample->sampleType->name : $this->sample->sample_type;
    }

    /**
     * Restituisce la stringa di presentazione per il conteggio dei file.
     */
    public function filesDisplay(): string
    {
        if ($this->isMasked() || $this->sample->files_count === 0) {
            return '—';
        }

        return $this->sample->files_count . ' file';
    }
}
