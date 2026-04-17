<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Client extends Model
{
    use LogsActivity;

    /**
     * Campi compilabili in massa.
     */
    protected $fillable = [
        'company_name',
        'first_name',
        'last_name',
        'type',
        'email',
        'phone',
        'pec',
        'address',
        'city',
        'province',
        'postal_code',
        'country',
        'tax_code',
        'vat_number',
        'sdi_code',
        'notes',
        'archived',
        'archived_at',
        'archived_by',
        'created_by',
    ];

    /**
     * Cast automatici.
     */
    protected $casts = [
        'archived'    => 'boolean',
        'archived_at' => 'datetime',
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
     * Un cliente ha molti campioni.
     */
    public function samples(): HasMany
    {
        return $this->hasMany(Sample::class);
    }

    /**
     * Utente che ha creato il cliente.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Utente che ha archiviato il cliente.
     */
    public function archivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'archived_by');
    }

    /**
     * Scope per i clienti attivi (non archiviati).
     */
    public function scopeActive($query)
    {
        return $query->where('archived', false);
    }

    /**
     * Scope per i clienti archiviati.
     */
    public function scopeArchived($query)
    {
        return $query->where('archived', true);
    }
}