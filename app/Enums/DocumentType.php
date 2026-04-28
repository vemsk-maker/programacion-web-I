<?php

namespace App\Enums;

enum DocumentType: string
{
    case Receipt      = 'receipt';
    case Order        = 'order';
    case TransferNote = 'transfer_note';
}
