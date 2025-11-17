<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Region extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'code', 
        'libelle',
        'description'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($region) {
            if (empty($region->code)) {
                $region->code = static::generateUniqueCode();
            }
        });
    }

    /**
     * Generate a unique region code.
     */
    protected static function generateUniqueCode(): string
    {
        $prefix = 'REG';
        $latest = static::where('code', 'like', $prefix . '%')
            ->orderBy('code', 'desc')
            ->first();

        if ($latest) {
            $number = (int) str_replace($prefix, '', $latest->code) + 1;
        } else {
            $number = 1;
        }

        return $prefix . str_pad($number, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Scope a query to only include active regions.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to search regions by libelle or code.
     */
    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where(function ($q) use ($search) {
            $q->where('libelle', 'like', "%{$search}%")
              ->orWhere('code', 'like', "%{$search}%");
        });
    }

    /**
     * Get all districts for the region.
     */
    public function districts(): HasMany
    {
        return $this->hasMany(District::class);
    }

    /**
     * Get all décès for the region.
     */
    public function deces(): HasMany
    {
        return $this->hasMany(Deces::class);
    }

    /**
     * Get all naissances for the region.
     */
    public function naissances(): HasMany
    {
        return $this->hasMany(Naissance::class);
    }

    /**
     * Get the count of districts for the region.
     */
    public function getDistrictsCountAttribute(): int
    {
        return $this->districts()->count();
    }

    /**
     * Get the count of naissances for the region.
     */
    public function getNaissancesCountAttribute(): int
    {
        return $this->naissances()->count();
    }

    /**
     * Get the count of décès for the region.
     */
    public function getDecesCountAttribute(): int
    {
        return $this->deces()->count();
    }

    /**
     * Get the region's full identifier (code + libelle).
     */
    public function getFullIdentifierAttribute(): string
    {
        return "{$this->code} - {$this->libelle}";
    }

    /**
     * Check if the region has any districts.
     */
    public function hasDistricts(): bool
    {
        return $this->districts_count > 0;
    }

    /**
     * Check if the region has any naissances.
     */
    public function hasNaissances(): bool
    {
        return $this->naissances_count > 0;
    }

    /**
     * Check if the region has any décès.
     */
    public function hasDeces(): bool
    {
        return $this->deces_count > 0;
    }

    /**
     * Get the latest naissances for the region.
     */
    public function latestNaissances(int $limit = 5)
    {
        return $this->naissances()
            ->with(['district', 'commune'])
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Get the latest décès for the region.
     */
    public function latestDeces(int $limit = 5)
    {
        return $this->deces()
            ->with(['district', 'commune', 'causeDeces'])
            ->latest()
            ->limit($limit)
            ->get();
    }
}