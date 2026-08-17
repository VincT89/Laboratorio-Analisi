<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class DocumentType extends Model
{
    protected $fillable = [
        'name',
        'code',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (DocumentType $documentType) {
            if ($documentType->code) {
                return;
            }

            $base = Str::slug($documentType->name, '_') ?: 'documento';
            $attempt = 0;

            do {
                $suffix = $attempt === 0 ? '' : "_{$attempt}";
                $code = substr($base, 0, 30 - strlen($suffix)).$suffix;
                $attempt++;
            } while (static::where('code', $code)->exists());

            $documentType->code = $code;
        });
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function files(): HasMany
    {
        return $this->hasMany(SampleFile::class);
    }
}
