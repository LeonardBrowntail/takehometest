<?php

namespace App\Policies;

class TransactionPolicy
{
    public function show()
    {
        return true;
    }
}
