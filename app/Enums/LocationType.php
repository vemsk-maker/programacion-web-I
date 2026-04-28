<?php

namespace App\Enums;

enum LocationType: string
{
    case Warehouse = 'warehouse';
    case Store     = 'store';
    case Waste     = 'waste';
}
