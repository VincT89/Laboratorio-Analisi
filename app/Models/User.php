<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles, LogsActivity;

    /**
     * Campi compilabili in massa.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_active',
    ];

    /**
     * Campi nascosti nella serializzazione.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Cast automatici.
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
        'is_active'         => 'boolean',
    ];

    /**
     * Configurazione activity log.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Campioni creati da questo utente.
     */
    public function createdSamples(): HasMany
    {
        return $this->hasMany(Sample::class, 'created_by');
    }

    /**
     * Campioni aggiornati da questo utente.
     */
    public function updatedSamples(): HasMany
    {
        return $this->hasMany(Sample::class, 'updated_by');
    }

    /**
     * File caricati da questo utente.
     */
    public function uploadedFiles(): HasMany
    {
        return $this->hasMany(SampleFile::class, 'uploaded_by');
    }

    /**
     * Clienti creati da questo utente.
     */
    public function createdClients(): HasMany
    {
        return $this->hasMany(Client::class, 'created_by');
    }

    /**
     * Verifica se l'utente è admin.
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    /**
     * Verifica se l'utente è staff.
     */
    public function isStaff(): bool
    {
        return $this->hasRole('staff');
    }
}