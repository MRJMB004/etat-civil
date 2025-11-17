<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class Commune extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'district_id',
        'code', 
        'libelle',
        'population',
        'superficie',
        'description'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'population' => 'integer',
        'superficie' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($commune) {
            if (empty($commune->code)) {
                $commune->code = static::generateUniqueCode($commune->district_id);
            }
        });

        // Empêcher la suppression si la commune a des fokontany, naissances ou décès
        static::deleting(function ($commune) {
            if ($commune->fokontany()->exists()) {
                throw new \Exception('Impossible de supprimer la commune : elle contient des fokontany.');
            }
            if ($commune->naissances()->exists()) {
                throw new \Exception('Impossible de supprimer la commune : elle contient des naissances.');
            }
            if ($commune->deces()->exists()) {
                throw new \Exception('Impossible de supprimer la commune : elle contient des décès.');
            }
        });
    }

    /**
     * Generate a unique commune code based on district.
     */
    protected static function generateUniqueCode(int $districtId): string
    {
        $district = District::with('region')->find($districtId);
        
        if ($district && $district->region) {
            $prefix = $district->region->code . '_' . $district->code . '_COM';
        } else {
            $prefix = 'COM';
        }
        
        $latest = static::where('code', 'like', $prefix . '%')
            ->orderBy('code', 'desc')
            ->first();

        if ($latest) {
            $number = (int) preg_replace('/[^0-9]/', '', $latest->code) + 1;
        } else {
            $number = 1;
        }

        return $prefix . str_pad($number, 2, '0', STR_PAD_LEFT);
    }

    /**
     * Scope a query to only include active communes.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to search communes by libelle or code.
     */
    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where(function ($q) use ($search) {
            $q->where('libelle', 'like', "%{$search}%")
              ->orWhere('code', 'like', "%{$search}%");
        });
    }

    /**
     * Scope a query to filter communes by district.
     */
    public function scopeByDistrict(Builder $query, int $districtId): Builder
    {
        return $query->where('district_id', $districtId);
    }

    /**
     * Scope a query to filter communes by region.
     */
    public function scopeByRegion(Builder $query, int $regionId): Builder
    {
        return $query->whereHas('district', function ($q) use ($regionId) {
            $q->where('region_id', $regionId);
        });
    }

    /**
     * Scope a query to include district and region data.
     */
    public function scopeWithHierarchy(Builder $query): Builder
    {
        return $query->with(['district.region']);
    }

    /**
     * Scope a query to order by population.
     */
    public function scopeOrderByPopulation(Builder $query, string $direction = 'desc'): Builder
    {
        return $query->orderBy('population', $direction);
    }

    /**
     * Get the district that owns the commune.
     */
    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    /**
     * Get all fokontany for the commune.
     */
    public function fokontany(): HasMany
    {
        return $this->hasMany(Fokontany::class);
    }

    /**
     * Get all décès for the commune.
     */
    public function deces(): HasMany
    {
        return $this->hasMany(Deces::class);
    }

    /**
     * Get all naissances for the commune.
     */
    public function naissances(): HasMany
    {
        return $this->hasMany(Naissance::class);
    }

    /**
     * Get the count of fokontany for the commune.
     */
    public function getFokontanyCountAttribute(): int
    {
        return $this->fokontany()->count();
    }

    /**
     * Get the count of naissances for the commune.
     */
    public function getNaissancesCountAttribute(): int
    {
        return $this->naissances()->count();
    }

    /**
     * Get the count of décès for the commune.
     */
    public function getDecesCountAttribute(): int
    {
        return $this->deces()->count();
    }

    /**
     * Get the density de population (hab/km²).
     */
    public function getDensityAttribute(): ?float
    {
        if ($this->population && $this->superficie && $this->superficie > 0) {
            return round($this->population / $this->superficie, 2);
        }
        
        return null;
    }

    /**
     * Get the commune's full identifier with hierarchy.
     */
    public function getFullIdentifierAttribute(): string
    {
        $hierarchy = [];
        
        if ($this->district) {
            $hierarchy[] = $this->district->libelle;
            if ($this->district->region) {
                $hierarchy[] = $this->district->region->libelle;
            }
        }
        
        $location = $hierarchy ? ' (' . implode(' - ', $hierarchy) . ')' : '';
        return "{$this->code} - {$this->libelle}{$location}";
    }

    /**
     * Check if the commune has any fokontany.
     */
    public function hasFokontany(): bool
    {
        return $this->fokontany_count > 0;
    }

    /**
     * Check if the commune has any naissances.
     */
    public function hasNaissances(): bool
    {
        return $this->naissances_count > 0;
    }

    /**
     * Check if the commune has any décès.
     */
    public function hasDeces(): bool
    {
        return $this->deces_count > 0;
    }

    /**
     * Check if the commune can be safely deleted.
     */
    public function canBeDeleted(): bool
    {
        return !$this->hasFokontany() && !$this->hasNaissances() && !$this->hasDeces();
    }

    /**
     * Get the latest naissances for the commune.
     */
    public function latestNaissances(int $limit = 5): Collection
    {
        return $this->naissances()
            ->with(['fokontany', 'nationalite'])
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Get the latest décès for the commune.
     */
    public function latestDeces(int $limit = 5): Collection
    {
        return $this->deces()
            ->with(['fokontany', 'causeDeces'])
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Get statistics for the commune.
     */
    public function getStatistics(): array
    {
        $currentYear = now()->year;
        
        return [
            'fokontany_count' => $this->fokontany_count,
            'naissances_count' => $this->naissances_count,
            'deces_count' => $this->deces_count,
            'naissances_this_year' => $this->naissances()->whereYear('created_at', $currentYear)->count(),
            'deces_this_year' => $this->deces()->whereYear('created_at', $currentYear)->count(),
            'population' => $this->population,
            'superficie' => $this->superficie,
            'density' => $this->density,
            'taux_naissance' => $this->population ? round(($this->naissances_count / $this->population) * 1000, 2) : null,
            'taux_mortalite' => $this->population ? round(($this->deces_count / $this->population) * 1000, 2) : null,
        ];
    }

    /**
     * Get the growth rate (naissances - décès).
     */
    public function getCroissanceAttribute(): int
    {
        return $this->naissances_count - $this->deces_count;
    }

    /**
     * Check if the commune has population data.
     */
    public function hasPopulationData(): bool
    {
        return !is_null($this->population) && $this->population > 0;
    }
}