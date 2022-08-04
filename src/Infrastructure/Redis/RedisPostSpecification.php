<?php

namespace DataProvider\Infrastructure\Redis;

use Domain\Model\Post;

interface RedisPostSpecification
{
    /**
     * @return boolean
     */
    public function specifies(Post $aPost);
}