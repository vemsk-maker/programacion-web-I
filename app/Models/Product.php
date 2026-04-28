<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'name',
        'description',
        'category_id',
        'use_batches',
        'unit_of_measure',
        'active',
    ];

    protected $casts = [
        'use_batches' => 'boolean',
        'active'      => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function barcodes(): HasMany
    {
        return $this->hasMany(ProductBarcode::class);
    }

    public function batches(): HasMany
    {
        return $this->hasMany(Batch::class);
    }

    public function stockCache(): HasMany
    {
        return $this->hasMany(StockCache::class);
    }
}
