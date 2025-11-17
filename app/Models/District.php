<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class District extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'region_id',
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

        static::creating(function ($district) {
            if (empty($district->code)) {
                $district->code = static::generateUniqueCode($district->region_id);
            }
        });

        // Empêcher la suppression si le district a des communes, naissances ou décès
        static::deleting(function ($district) {
            if ($district->communes()->exists()) {
                throw new \Exception('Impossible de supprimer le district : il contient des communes.');
            }
            if ($district->naissances()->exists()) {
                throw new \Exception('Impossible de supprimer le district : il contient des naissances.');
            }
            if ($district->deces()->exists()) {
                throw new \Exception('Impossible de supprimer le district : il contient des décès.');
            }
        });
    }

    /**
     * Generate a unique district code based on region.
     */
    protected static function generateUniqueCode(int $regionId): string
    {
        $region = Region::find($regionId);
        $prefix = $region ? $region->code . '_DIST' : 'DIST';
        
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
     * Scope a query to only include active districts.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to search districts by libelle or code.
     */
    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where(function ($q) use ($search) {
            $q->where('libelle', 'like', "%{$search}%")
              ->orWhere('code', 'like', "%{$search}%");
        });
    }

    /**
     * Scope a query to filter districts by region.
     */
    public function scopeByRegion(Builder $query, int $regionId): Builder
    {
        return $query->where('region_id', $regionId);
    }

    /**
     * Scope a query to include region data.
     */
    public function scopeWithRegion(Builder $query): Builder
    {
        return $query->with('region');
    }

    /**
     * Get the region that owns the district.
     */
    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    /**
     * Get all communes for the district.
     */
    public function communes(): HasMany
    {
        return $this->hasMany(Commune::class);
    }

    /**
     * Get all décès for the district.
     */
    public function deces(): HasMany
    {
        return $this->hasMany(Deces::class);
    }

    /**
     * Get all naissances for the district.
     */
    public function naissances(): HasMany
    {
        return $this->hasMany(Naissance::class);
    }

    /**
     * Get the count of communes for the district.
     */
    public function getCommunesCountAttribute(): int
    {
        return $this->communes()->count();
    }

    /**
     * Get the count of naissances for the district.
     */
    public function getNaissancesCountAttribute(): int
    {
        return $this->naissances()->count();
    }

    /**
     * Get the count of décès for the district.
     */
    public function getDecesCountAttribute(): int
    {
        return $this->deces()->count();
    }

    /**
     * Get the district's full identifier (code + libelle + région).
     */
    public function getFullIdentifierAttribute(): string
    {
        $regionName = $this->region ? $this->region->libelle : 'Région inconnue';
        return "{$this->code} - {$this->libelle} ({$regionName})";
    }

    /**
     * Check if the district has any communes.
     */
    public function hasCommunes(): bool
    {
        return $this->communes_count > 0;
    }

    /**
     * Check if the district has any naissances.
     */
    public function hasNaissances(): bool
    {
        return $this->naissances_count > 0;
    }

    /**
     * Check if the district has any décès.
     */
    public function hasDeces(): bool
    {
        return $this->deces_count > 0;
    }

    /**
     * Check if the district can be safely deleted.
     */
    public function canBeDeleted(): bool
    {
        return !$this->hasCommunes() && !$this->hasNaissances() && !$this->hasDeces();
    }

    /**
     * Get the latest naissances for the district.
     */
    public function latestNaissances(int $limit = 5): Collection
    {
        return $this->naissances()
            ->with(['commune', 'fokontany'])
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Get the latest décès for the district.
     */
    public function latestDeces(int $limit = 5): Collection
    {
        return $this->deces()
            ->with(['commune', 'fokontany', 'causeDeces'])
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Get statistics for the district.
     */
    public function getStatistics(): array
    {
        return [
            'communes_count' => $this->communes_count,
            'naissances_count' => $this->naissances_count,
            'deces_count' => $this->deces_count,
            'naissances_this_year' => $this->naissances()->whereYear('created_at', now()->year)->count(),
            'deces_this_year' => $this->deces()->whereYear('created_at', now()->year)->count(),
        ];
    }
}