<?php

namespace DataProvider\Infrastructure\Redis;

class RedisPostSpecificationFactory implements PostSpecificationFactory
{
    public function createLatestPosts(\DateTime $since)
    {
        return new RedisLatestPostSpecification($since);
    }
}