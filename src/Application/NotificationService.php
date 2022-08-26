<?php

namespace App\Application;

use App\Domain\Coingecko\Token;

class NotificationService
{

    public function sendNotfication(Token $token)
    {
        echo $token->name()->asString() . PHP_EOL;
    }
}