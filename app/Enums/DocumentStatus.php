<?php

namespace App\Enums;

enum DocumentStatus: string
{
    case Open      = 'open';
    case Closed    = 'closed';
    case Cancelled = 'cancelled';
}
