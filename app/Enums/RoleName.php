<?php

namespace App\Enums;

enum RoleName: string
{
    case Master           = 'master';
    case Admin            = 'admin';
    case WarehouseManager = 'warehouse_manager';
    case Cashier          = 'cashier';
    case Viewer           = 'viewer';
}
