<?php

namespace DataProvider\Infrastructure\Redis;

use Predis\Client;

class RedisRepository
{
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

}