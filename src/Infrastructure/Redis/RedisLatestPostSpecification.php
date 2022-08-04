<?php

namespace DataProvider\Infrastructure\Redis;

class RedisLatestPostSpecification
{
    private $since;

    public function __construct(\DateTime $since)
    {
        $this->since = $since;
    }

    public function specifies(Post $aPost)
    {
        return $aPost->createdAt() > $this->since;
    }
}