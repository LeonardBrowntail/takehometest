<?php

namespace App\Enums;

enum LedgerType: string
{
    case DEBIT = 'debit';
    case CREDIT = 'credit';
}
