<?php

namespace App\Models;

use App\Enums\DocumentStatus;
use App\Enums\DocumentType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Document extends Model
{
    protected $fillable = [
        'movement_group_id',
        'document_type',
        'doc_number',
        'client_name',
        'client_nit',
        'total_amount',
        'status',
        'printed_at',
    ];

    protected $casts = [
        'document_type' => DocumentType::class,
        'status'        => DocumentStatus::class,
        'total_amount'  => 'decimal:2',
        'printed_at'    => 'datetime',
    ];

    public function movementGroup(): BelongsTo
    {
        return $this->belongsTo(MovementGroup::class);
    }
}
