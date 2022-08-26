<?php

namespace DataProvider\Infrastructure\Redis;

interface TokenFactory
{

    public function createTokenFrom(mixed $webElement);
}