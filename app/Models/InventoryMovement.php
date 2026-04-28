<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryMovement extends Model
{
    protected $fillable = [
        'group_id',
        'product_id',
        'batch_id',
        'from_location_id',
        'to_location_id',
        'quantity',
        'unit_cost',
    ];

    protected $casts = [
        'quantity'  => 'decimal:2',
        'unit_cost' => 'decimal:2',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(MovementGroup::class, 'group_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function fromLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'from_location_id');
    }

    public function toLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'to_location_id');
    }
}
