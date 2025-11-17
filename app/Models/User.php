<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * User roles constants
     */
    public const ROLE_ADMIN = 'admin';
    public const ROLE_AGENT_REGISTRE = 'agent_registre';
    public const ROLE_AGENT_SAISIE = 'agent_saisie';
    public const ROLE_CONSULTANT = 'consultant';
    public const ROLE_GUEST = 'guest';

    /**
     * User status constants
     */
    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';
    public const STATUS_SUSPENDED = 'suspended';
    public const STATUS_PENDING = 'pending';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'username',
        'phone',
        'avatar',
        'role',
        'status',
        'last_login_at',
        'last_login_ip',
        'email_verified_at',
        'region_id',
        'district_id',
        'commune_id',
        'fokontany_id',
        'settings',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'settings' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            // Set default role if not provided
            if (empty($user->role)) {
                $user->role = self::ROLE_GUEST;
            }

            // Set default status if not provided
            if (empty($user->status)) {
                $user->status = self::STATUS_PENDING;
            }

            // Generate username if not provided
            if (empty($user->username) && !empty($user->email)) {
                $user->username = static::generateUsername($user->email);
            }
        });

        // Log the deletion
        static::deleting(function ($user) {
            if ($user->isAdmin()) {
                throw new \Exception('Impossible de supprimer un compte administrateur.');
            }
        });
    }

    /**
     * Generate username from email
     */
    protected static function generateUsername(string $email): string
    {
        $username = strstr($email, '@', true);
        $baseUsername = $username;
        $counter = 1;

        while (static::where('username', $username)->exists()) {
            $username = $baseUsername . $counter;
            $counter++;
        }

        return $username;
    }

    // ========== RELATIONS ==========

    /**
     * Get the region assigned to the user
     */
    public function region(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    /**
     * Get the district assigned to the user
     */
    public function district(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    /**
     * Get the commune assigned to the user
     */
    public function commune(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Commune::class);
    }

    /**
     * Get the fokontany assigned to the user
     */
    public function fokontany(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Fokontany::class);
    }

    /**
     * Get the naissances created by the user
     */
    public function naissances(): HasMany
    {
        return $this->hasMany(Naissance::class, 'created_by');
    }

    /**
     * Get the deces created by the user
     */
    public function deces(): HasMany
    {
        return $this->hasMany(Deces::class, 'created_by');
    }

    /**
     * Get the user's activity logs
     */
    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    // ========== SCOPES ==========

    /**
     * Scope a query to only include active users.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope a query to only include users by role.
     */
    public function scopeByRole(Builder $query, string $role): Builder
    {
        return $query->where('role', $role);
    }

    /**
     * Scope a query to only include administrators.
     */
    public function scopeAdmins(Builder $query): Builder
    {
        return $query->where('role', self::ROLE_ADMIN);
    }

    /**
     * Scope a query to only include registry agents.
     */
    public function scopeRegistryAgents(Builder $query): Builder
    {
        return $query->where('role', self::ROLE_AGENT_REGISTRE);
    }

    /**
     * Scope a query to only include data entry agents.
     */
    public function scopeDataEntryAgents(Builder $query): Builder
    {
        return $query->where('role', self::ROLE_AGENT_SAISIE);
    }

    /**
     * Scope a query to only include consultants.
     */
    public function scopeConsultants(Builder $query): Builder
    {
        return $query->where('role', self::ROLE_CONSULTANT);
    }

    /**
     * Scope a query to search users.
     */
    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('username', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%");
        });
    }

    /**
     * Scope a query to filter users by region.
     */
    public function scopeByRegion(Builder $query, int $regionId): Builder
    {
        return $query->where('region_id', $regionId);
    }

    /**
     * Scope a query to filter users by district.
     */
    public function scopeByDistrict(Builder $query, int $districtId): Builder
    {
        return $query->where('district_id', $districtId);
    }

    /**
     * Scope a query to include geographical relations.
     */
    public function scopeWithGeography(Builder $query): Builder
    {
        return $query->with(['region', 'district', 'commune', 'fokontany']);
    }

    /**
     * Scope a query for users who logged in recently.
     */
    public function scopeRecentlyActive(Builder $query, int $days = 7): Builder
    {
        return $query->where('last_login_at', '>=', now()->subDays($days));
    }

    // ========== ACCESSORS ==========

    /**
     * Get the user's full geographical assignment
     */
    public function getGeographicalAssignmentAttribute(): string
    {
        $assignment = [];

        if ($this->fokontany) {
            $assignment[] = $this->fokontany->libelle;
        }
        if ($this->commune) {
            $assignment[] = $this->commune->libelle;
        }
        if ($this->district) {
            $assignment[] = $this->district->libelle;
        }
        if ($this->region) {
            $assignment[] = $this->region->libelle;
        }

        return $assignment ? implode(' > ', $assignment) : 'Aucune affectation';
    }

    /**
     * Get the user's role as display text
     */
    public function getRoleTextAttribute(): string
    {
        switch($this->role) {
            case self::ROLE_ADMIN:
                return 'Administrateur';
            case self::ROLE_AGENT_REGISTRE:
                return 'Agent de registre';
            case self::ROLE_AGENT_SAISIE:
                return 'Agent de saisie';
            case self::ROLE_CONSULTANT:
                return 'Consultant';
            case self::ROLE_GUEST:
                return 'Invité';
            default:
                return 'Rôle inconnu';
        }
    }

    /**
     * Get the user's status as display text
     */
    public function getStatusTextAttribute(): string
    {
        switch($this->status) {
            case self::STATUS_ACTIVE:
                return 'Actif';
            case self::STATUS_INACTIVE:
                return 'Inactif';
            case self::STATUS_SUSPENDED:
                return 'Suspendu';
            case self::STATUS_PENDING:
                return 'En attente';
            default:
                return 'Statut inconnu';
        }
    }

    /**
     * Get the user's avatar URL
     */
    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            return asset('storage/avatars/' . $this->avatar);
        }

        // Generate default avatar based on name
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&color=7F9CF5&background=EBF4FF';
    }

    /**
     * Check if user is online (active in last 5 minutes)
     */
    public function getIsOnlineAttribute(): bool
    {
        return $this->last_login_at && $this->last_login_at->gt(now()->subMinutes(5));
    }

    /**
     * Get the user's activity level
     */
    public function getActivityLevelAttribute(): string
    {
        if (!$this->last_login_at) {
            return 'never';
        }

        $daysSinceLastLogin = $this->last_login_at->diffInDays(now());

        if ($daysSinceLastLogin < 1) {
            return 'very_high';
        } elseif ($daysSinceLastLogin < 7) {
            return 'high';
        } elseif ($daysSinceLastLogin < 30) {
            return 'medium';
        } elseif ($daysSinceLastLogin < 90) {
            return 'low';
        } else {
            return 'very_low';
        }
    }

    /**
     * Get user settings with defaults
     */
    public function getSettingsAttribute($value): array
    {
        $defaultSettings = [
            'language' => 'fr',
            'timezone' => 'Indian/Antananarivo',
            'date_format' => 'd/m/Y',
            'records_per_page' => 25,
            'notifications' => [
                'email' => true,
                'sms' => false,
                'push' => true,
            ],
            'dashboard' => [
                'show_stats' => true,
                'recent_activity' => true,
                'quick_actions' => true,
            ]
        ];

        $userSettings = $value ? json_decode($value, true) : [];

        return array_merge($defaultSettings, $userSettings);
    }

    // ========== METHODS ==========

    /**
     * Check if user has a specific role
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Check if user is administrator
     */
    public function isAdmin(): bool
    {
        return $this->hasRole(self::ROLE_ADMIN);
    }

    /**
     * Check if user is registry agent
     */
    public function isRegistryAgent(): bool
    {
        return $this->hasRole(self::ROLE_AGENT_REGISTRE);
    }

    /**
     * Check if user is data entry agent
     */
    public function isDataEntryAgent(): bool
    {
        return $this->hasRole(self::ROLE_AGENT_SAISIE);
    }

    /**
     * Check if user is consultant
     */
    public function isConsultant(): bool
    {
        return $this->hasRole(self::ROLE_CONSULTANT);
    }

    /**
     * Check if user can manage users
     */
    public function canManageUsers(): bool
    {
        return $this->isAdmin();
    }

    /**
     * Check if user can manage records
     */
    public function canManageRecords(): bool
    {
        return in_array($this->role, [
            self::ROLE_ADMIN,
            self::ROLE_AGENT_REGISTRE,
            self::ROLE_AGENT_SAISIE
        ]);
    }

    /**
     * Check if user can view statistics
     */
    public function canViewStatistics(): bool
    {
        return in_array($this->role, [
            self::ROLE_ADMIN,
            self::ROLE_CONSULTANT,
            self::ROLE_AGENT_REGISTRE
        ]);
    }

    /**
     * Check if user is active
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Activate user account
     */
    public function activate(): bool
    {
        return $this->update([
            'status' => self::STATUS_ACTIVE,
            'email_verified_at' => $this->email_verified_at ?: now()
        ]);
    }

    /**
     * Deactivate user account
     */
    public function deactivate(): bool
    {
        return $this->update(['status' => self::STATUS_INACTIVE]);
    }

    /**
     * Suspend user account
     */
    public function suspend(): bool
    {
        return $this->update(['status' => self::STATUS_SUSPENDED]);
    }

    /**
     * Record user login
     */
    public function recordLogin(string $ip): bool
    {
        return $this->update([
            'last_login_at' => now(),
            'last_login_ip' => $ip
        ]);
    }

    /**
     * Get user's permissions
     */
    public function getPermissions(): array
    {
        switch($this->role) {
            case self::ROLE_ADMIN:
                return [
                    'users.manage',
                    'records.manage',
                    'records.view',
                    'records.create',
                    'records.edit',
                    'records.delete',
                    'statistics.view',
                    'reports.generate',
                    'settings.manage'
                ];
            case self::ROLE_AGENT_REGISTRE:
                return [
                    'records.manage',
                    'records.view',
                    'records.create',
                    'records.edit',
                    'records.delete',
                    'statistics.view',
                    'reports.generate'
                ];
            case self::ROLE_AGENT_SAISIE:
                return [
                    'records.view',
                    'records.create',
                    'records.edit',
                    'statistics.view'
                ];
            case self::ROLE_CONSULTANT:
                return [
                    'records.view',
                    'statistics.view',
                    'reports.generate'
                ];
            case self::ROLE_GUEST:
                return [
                    'records.view'
                ];
            default:
                return [];
        }
    }

    /**
     * Check if user has specific permission
     */
    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->getPermissions());
    }

    /**
     * Get user's activity statistics
     */
    public function getActivityStatistics(): array
    {
        $last30Days = now()->subDays(30);

        return [
            'naissances_created' => $this->naissances()
                ->where('created_at', '>=', $last30Days)
                ->count(),
            'deces_created' => $this->deces()
                ->where('created_at', '>=', $last30Days)
                ->count(),
            'total_created' => $this->naissances()
                ->where('created_at', '>=', $last30Days)
                ->count() + $this->deces()
                ->where('created_at', '>=', $last30Days)
                ->count(),
            'last_activity' => $this->last_login_at ? $this->last_login_at->diffForHumans() : 'Jamais',
        ];
    }

    // ========== STATIC METHODS ==========

    /**
     * Get all available roles
     */
    public static function getRoles(): array
    {
        return [
            self::ROLE_ADMIN => 'Administrateur',
            self::ROLE_AGENT_REGISTRE => 'Agent de registre',
            self::ROLE_AGENT_SAISIE => 'Agent de saisie',
            self::ROLE_CONSULTANT => 'Consultant',
            self::ROLE_GUEST => 'Invité',
        ];
    }

    /**
     * Get all available statuses
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_ACTIVE => 'Actif',
            self::STATUS_INACTIVE => 'Inactif',
            self::STATUS_SUSPENDED => 'Suspendu',
            self::STATUS_PENDING => 'En attente',
        ];
    }

    /**
     * Get users statistics
     */
    public static function getStatistics(): array
    {
        return [
            'total' => self::count(),
            'active' => self::active()->count(),
            'admins' => self::admins()->count(),
            'registry_agents' => self::registryAgents()->count(),
            'data_entry_agents' => self::dataEntryAgents()->count(),
            'consultants' => self::consultants()->count(),
            'recently_active' => self::recentlyActive(7)->count(),
        ];
    }

    /**
     * Find user by username or email
     */
    public static function findByUsernameOrEmail(string $identifier): ?self
    {
        return self::where('username', $identifier)
            ->orWhere('email', $identifier)
            ->first();
    }
}