<?php

namespace App\Enums;

enum MovementType: string
{
    case Purchase   = 'purchase';
    case Sale       = 'sale';
    case Transfer   = 'transfer';
    case Adjustment = 'adjustment';
    case Waste      = 'waste';
}
