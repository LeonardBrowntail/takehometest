<?php

namespace App\Policies;

class WalletPolicy
{
    public function deposit()
    {
        return true;
    }

    public function transfer()
    {
        return true;
    }

    public function show()
    {
        return true;
    }
}
