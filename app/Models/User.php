<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\RoleName;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'active'            => 'boolean',
        ];
    }

    // ── Relations ────────────────────────────────────────────────────────────

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function locations(): BelongsToMany
    {
        return $this->belongsToMany(Location::class, 'user_locations');
    }

    public function movementGroups(): HasMany
    {
        return $this->hasMany(MovementGroup::class);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function isAdmin(): bool
    {
        return in_array($this->role?->name, [RoleName::Master, RoleName::Admin], true);
    }

    public function isCashier(): bool
    {
        return $this->role?->name === RoleName::Cashier;
    }

    public function hasLocationAccess(int $locationId): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        return $this->locations->contains('id', $locationId);
    }
}
