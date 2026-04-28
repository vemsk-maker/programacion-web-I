<?php

namespace App\Models;

use App\Enums\MovementType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class MovementGroup extends Model
{
    protected $fillable = [
        'type',
        'reference_doc',
        'user_id',
        'origin_location_id',
        'notes',
    ];

    protected $casts = [
        'type' => MovementType::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function originLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'origin_location_id');
    }

    public function movements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class, 'group_id');
    }

    public function document(): HasOne
    {
        return $this->hasOne(Document::class, 'movement_group_id');
    }
}
