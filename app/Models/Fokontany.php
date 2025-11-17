<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class Fokontany extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'fokontany';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'commune_id',
        'code', 
        'libelle',
        'idfkt',
        'population',
        'nombre_menages',
        'chef_fokontany',
        'contact_chef',
        'adresse',
        'description'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'population' => 'integer',
        'nombre_menages' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($fokontany) {
            // Générer un code unique si non fourni
            if (empty($fokontany->code)) {
                $fokontany->code = static::generateUniqueCode($fokontany->commune_id);
            }
            
            // Générer un IDFKT unique si non fourni
            if (empty($fokontany->idfkt)) {
                $fokontany->idfkt = static::generateUniqueIdfkt();
            }
        });

        // Empêcher la suppression si le fokontany a des naissances ou décès
        static::deleting(function ($fokontany) {
            if ($fokontany->naissances()->exists()) {
                throw new \Exception('Impossible de supprimer le fokontany : il contient des naissances enregistrées.');
            }
            if ($fokontany->deces()->exists()) {
                throw new \Exception('Impossible de supprimer le fokontany : il contient des décès enregistrés.');
            }
        });
    }

    /**
     * Generate a unique fokontany code based on commune.
     */
    protected static function generateUniqueCode(int $communeId): string
    {
        $commune = Commune::with(['district.region'])->find($communeId);
        
        if ($commune && $commune->district && $commune->district->region) {
            $prefix = $commune->district->region->code . '_' . 
                     $commune->district->code . '_' . 
                     $commune->code . '_FKT';
        } else {
            $prefix = 'FKT';
        }
        
        $latest = static::where('code', 'like', $prefix . '%')
            ->orderBy('code', 'desc')
            ->first();

        if ($latest) {
            $number = (int) preg_replace('/[^0-9]/', '', $latest->code) + 1;
        } else {
            $number = 1;
        }

        return $prefix . str_pad($number, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Generate a unique IDFKT.
     */
    protected static function generateUniqueIdfkt(): string
    {
        do {
            $idfkt = 'FKT_' . strtoupper(uniqid());
        } while (static::where('idfkt', $idfkt)->exists());

        return $idfkt;
    }

    /**
     * Scope a query to only include active fokontany.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to search fokontany by libelle, code or chef.
     */
    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where(function ($q) use ($search) {
            $q->where('libelle', 'like', "%{$search}%")
              ->orWhere('code', 'like', "%{$search}%")
              ->orWhere('idfkt', 'like', "%{$search}%")
              ->orWhere('chef_fokontany', 'like', "%{$search}%");
        });
    }

    /**
     * Scope a query to filter fokontany by commune.
     */
    public function scopeByCommune(Builder $query, int $communeId): Builder
    {
        return $query->where('commune_id', $communeId);
    }

    /**
     * Scope a query to filter fokontany by district.
     */
    public function scopeByDistrict(Builder $query, int $districtId): Builder
    {
        return $query->whereHas('commune', function ($q) use ($districtId) {
            $q->where('district_id', $districtId);
        });
    }

    /**
     * Scope a query to filter fokontany by region.
     */
    public function scopeByRegion(Builder $query, int $regionId): Builder
    {
        return $query->whereHas('commune.district', function ($q) use ($regionId) {
            $q->where('region_id', $regionId);
        });
    }

    /**
     * Scope a query to include full hierarchy data.
     */
    public function scopeWithHierarchy(Builder $query): Builder
    {
        return $query->with(['commune.district.region']);
    }

    /**
     * Scope a query to order by population.
     */
    public function scopeOrderByPopulation(Builder $query, string $direction = 'desc'): Builder
    {
        return $query->orderBy('population', $direction);
    }

    /**
     * Get the commune that owns the fokontany.
     */
    public function commune(): BelongsTo
    {
        return $this->belongsTo(Commune::class);
    }

    /**
     * Get all décès for the fokontany.
     */
    public function deces(): HasMany
    {
        return $this->hasMany(Deces::class);
    }

    /**
     * Get all naissances for the fokontany.
     */
    public function naissances(): HasMany
    {
        return $this->hasMany(Naissance::class);
    }

    /**
     * Get the count of naissances for the fokontany.
     */
    public function getNaissancesCountAttribute(): int
    {
        return $this->naissances()->count();
    }

    /**
     * Get the count of décès for the fokontany.
     */
    public function getDecesCountAttribute(): int
    {
        return $this->deces()->count();
    }

    /**
     * Get the count of ménages for the fokontany.
     */
    public function getMenagesCountAttribute(): int
    {
        return $this->nombre_menages ?? 0;
    }

    /**
     * Get the average population per ménage.
     */
    public function getMoyenneParMenageAttribute(): ?float
    {
        if ($this->population && $this->nombre_menages && $this->nombre_menages > 0) {
            return round($this->population / $this->nombre_menages, 2);
        }
        
        return null;
    }

    /**
     * Get the fokontany's full identifier with complete hierarchy.
     */
    public function getFullIdentifierAttribute(): string
    {
        $hierarchy = [];
        
        if ($this->commune) {
            $hierarchy[] = $this->commune->libelle;
            if ($this->commune->district) {
                $hierarchy[] = $this->commune->district->libelle;
                if ($this->commune->district->region) {
                    $hierarchy[] = $this->commune->district->region->libelle;
                }
            }
        }
        
        $location = $hierarchy ? ' (' . implode(' - ', $hierarchy) . ')' : '';
        return "{$this->code} - {$this->libelle}{$location}";
    }

    /**
     * Get the fokontany's short identifier.
     */
    public function getShortIdentifierAttribute(): string
    {
        return "{$this->code} - {$this->libelle}";
    }

    /**
     * Check if the fokontany has any naissances.
     */
    public function hasNaissances(): bool
    {
        return $this->naissances_count > 0;
    }

    /**
     * Check if the fokontany has any décès.
     */
    public function hasDeces(): bool
    {
        return $this->deces_count > 0;
    }

    /**
     * Check if the fokontany has population data.
     */
    public function hasPopulationData(): bool
    {
        return !is_null($this->population) && $this->population > 0;
    }

    /**
     * Check if the fokontany can be safely deleted.
     */
    public function canBeDeleted(): bool
    {
        return !$this->hasNaissances() && !$this->hasDeces();
    }

    /**
     * Get the latest naissances for the fokontany.
     */
    public function latestNaissances(int $limit = 5): Collection
    {
        return $this->naissances()
            ->with(['nationalite', 'profession'])
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Get the latest décès for the fokontany.
     */
    public function latestDeces(int $limit = 5): Collection
    {
        return $this->deces()
            ->with(['causeDeces', 'profession'])
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Get statistics for the fokontany.
     */
    public function getStatistics(): array
    {
        $currentYear = now()->year;
        
        return [
            'naissances_count' => $this->naissances_count,
            'deces_count' => $this->deces_count,
            'naissances_this_year' => $this->naissances()->whereYear('created_at', $currentYear)->count(),
            'deces_this_year' => $this->deces()->whereYear('created_at', $currentYear)->count(),
            'population' => $this->population,
            'nombre_menages' => $this->nombre_menages,
            'moyenne_par_menage' => $this->moyenne_par_menage,
            'taux_naissance' => $this->population ? round(($this->naissances_count / $this->population) * 1000, 2) : null,
            'taux_mortalite' => $this->population ? round(($this->deces_count / $this->population) * 1000, 2) : null,
            'croissance_naturelle' => $this->naissances_count - $this->deces_count,
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
     * Get contact information for the fokontany.
     */
    public function getContactInfo(): array
    {
        return [
            'chef_fokontany' => $this->chef_fokontany,
            'contact_chef' => $this->contact_chef,
            'adresse' => $this->adresse,
        ];
    }

    /**
     * Check if contact information is available.
     */
    public function hasContactInfo(): bool
    {
        return !empty($this->chef_fokontany) || !empty($this->contact_chef) || !empty($this->adresse);
    }
}