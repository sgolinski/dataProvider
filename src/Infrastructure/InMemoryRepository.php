<?php

namespace App\Infrastructure;

use App\Application\FindAllNotComplete;
use App\Domain\Coingecko\Token;

class InMemoryRepository
{
    private array $tokensInCache = [];

    public function add(Token $token)
    {
        $this->tokensInCache[$token->name()->asString()] = $token;
    }

    public function findAll(FindAllNotComplete $command): array
    {
        return $this->tokensInCache;
    }
}