<?php

namespace DataProvider\Reader;


use DataProvider\Datastore\Redis;
use DataProvider\Entity\Token;
use DataProvider\ValueObjects\Name;

class RedisReader implements Reader
{
    public static function readTokenByName(string $name): ?Token
    {
        $token = Redis::get_redis()->get($name);
        if ($token) {
            return unserialize($token);
        }
        return null;
    }

    public static function findKey(string $key): bool
    {
        return Redis::get_redis()->exists($key);
    }
}