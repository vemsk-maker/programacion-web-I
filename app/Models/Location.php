<?php

namespace App\Models;

use App\Enums\LocationType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    protected $fillable = [
        'name',
        'type',
        'parent_id',
        'active',
    ];

    protected $casts = [
        'type'   => LocationType::class,
        'active' => 'boolean',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Location::class, 'parent_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_locations');
    }

    public function stockCache(): HasMany
    {
        return $this->hasMany(StockCache::class);
    }
}
