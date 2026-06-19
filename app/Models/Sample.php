<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

/**
 * Lo stato del campione è gestito tramite metodi di workflow (accept, complete).
 * Le archiviazione operano in cascata sui file e blindano l'entità.
 */
class Sample extends Model
{
    use LogsActivity;

    /**
     * Campi compilabili in massa.
     */
    protected $fillable = [
        'code',
        'client_id',
        'sample_type_id',
        'collected_at',
        'sample_type',
        'collection_site',
        'collected_by',
        'accepted_at',
        'status',
        'notes',
        'archived',
        'archived_at',
        'archived_by',
        'created_by',
        'updated_by',
        'lab_archived_by_name',
        'container_type_id',
        'conservation_status',
        'sample_quantity',
        'sample_quantity_unit',
        'code_progressive',
        'code_year',
    ];

    /**
     * Cast automatici.
     */
    protected $casts = [
        'collected_at' => 'date',
        'accepted_at'  => 'date',
        'archived'     => 'boolean',
        'archived_at'  => 'datetime',
        'sample_quantity' => 'decimal:3',
    ];

    /**
     * Configurazione activity log.
     * Registra tutte le modifiche ai campi fillable.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Il campione appartiene a un cliente.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Il campione appartiene a un SampleType.
     */
    public function sampleType(): BelongsTo
    {
        return $this->belongsTo(SampleType::class);
    }

    /**
     * Il campione appartiene a un ContainerType.
     */
    public function containerType(): BelongsTo
    {
        return $this->belongsTo(ContainerType::class);
    }

    /**
     * Il campione ha molti file.
     */
    public function files(): HasMany
    {
        return $this->hasMany(SampleFile::class);
    }

    /**
     * Utente che ha creato il campione.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Utente che ha aggiornato il campione.
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Utente che ha archiviato il campione.
     */
    public function archivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'archived_by');
    }

    /**
     * Scope per i campioni attivi (non archiviati).
     */
    public function scopeActive($query)
    {
        return $query->where('archived', false);
    }

    /**
     * Scope per i campioni archiviati.
     */
    public function scopeArchived($query)
    {
        return $query->where('archived', true);
    }

    /**
     * Scope per filtrare per stato.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function canBeAccepted(): bool
    {
        return !$this->archived && 
               $this->status === 'collected' && 
               !$this->isSensitiveIncomplete();
    }

    public function canBeCompleted(): bool
    {
        return !$this->archived && 
               $this->status === 'accepted' && 
               !$this->isSensitiveIncomplete();
    }

    public function canBeRejected(): bool
    {
        return !$this->archived && $this->status !== 'completed' && $this->status !== 'rejected';
    }

    /**
     * Controlla se il campione ha privacy elevata (basato sul tipo).
     */
    public function isSensitive(): bool
    {
        return (bool) ($this->sampleType?->is_sensitive ?? false);
    }

    /**
     * Controlla se il campione è sensibile ma è in attesa dei dati obbligatori da parte dell'admin.
     */
    public function isSensitiveIncomplete(): bool
    {
        return $this->isSensitive() && is_null($this->client_id);
    }

    /**
     * Scope per l'admin per recuperare le preregistrazioni orfane.
     */
    public function scopeSensitiveIncomplete($query)
    {
        return $query->whereNull('client_id')->whereHas('sampleType', function ($q) {
            $q->where('is_sensitive', true);
        });
    }
}