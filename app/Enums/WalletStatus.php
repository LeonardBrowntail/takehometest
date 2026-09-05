<?php

namespace App\Enums;

enum WalletStatus: string
{
    case ACTIVE = 'active';
    case FROZEN = 'frozen';
    case SUSPENDED = 'suspended';
}
