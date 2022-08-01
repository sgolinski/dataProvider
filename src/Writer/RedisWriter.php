<?php

namespace DataProvider\Writer;

use DataProvider\Datastore\Redis;
use DataProvider\Entity\Token;
use Exception;

class RedisWriter
{
    public static function writeToRedis(array $tokens): void
    {
        foreach ($tokens as $token) {
            try {
                assert($token instanceof Token);
                Redis::get_redis()->set($token->getName()->asString(), serialize($token));
            } catch (Exception $exception) {
                echo $exception->getMessage();
            }
        }
        Redis::get_redis()->save();
    }

}