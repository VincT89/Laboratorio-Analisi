<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class SampleFile extends Model
{
    use LogsActivity;

    /**
     * Campi compilabili in massa.
     */
    protected $fillable = [
        'sample_id',
        'original_name',
        'type',
        'path',
        'mime_type',
        'extension',
        'size',
        'description',
        'archived',
        'archived_at',
        'archived_by',
        'uploaded_by',
    ];

    /**
     * Cast automatici.
     */
    protected $casts = [
        'archived'    => 'boolean',
        'archived_at' => 'datetime',
        'size'        => 'integer',
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
     * Il file appartiene a un campione.
     */
    public function sample(): BelongsTo
    {
        return $this->belongsTo(Sample::class);
    }

    /**
     * Utente che ha caricato il file.
     */
    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Utente che ha archiviato il file.
     */
    public function archivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'archived_by');
    }

    /**
     * Scope per i file attivi (non archiviati).
     */
    public function scopeActive($query)
    {
        return $query->where('archived', false);
    }

    /**
     * Scope per i file archiviati.
     */
    public function scopeArchived($query)
    {
        return $query->where('archived', true);
    }

    public function isDownloadable(): bool
    {
        return !$this->archived && !$this->sample->archived;
    }

    /**
     * Restituisce la dimensione del file in formato leggibile.
     */
    public function getHumanSizeAttribute(): string
    {
        $bytes = $this->size;

        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        }

        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }

        return $bytes . ' B';
    }
}