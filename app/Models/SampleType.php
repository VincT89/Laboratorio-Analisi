<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SampleType extends Model
{
    /** @use HasFactory<\Database\Factories\SampleTypeFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'is_active',
        'is_sensitive',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_sensitive' => 'boolean',
    ];

    protected static function booted()
    {
        static::creating(function ($sampleType) {
            if (empty($sampleType->slug)) {
                $baseSlug = \Illuminate\Support\Str::slug($sampleType->name);
                $slug = $baseSlug;
                $count = 1;

                while (static::where('slug', $slug)->exists()) {
                    $slug = $baseSlug . '-' . $count;
                    $count++;
                }

                $sampleType->slug = $slug;
            }
        });
    }

    public function samples()
    {
        return $this->hasMany(Sample::class);
    }
}
