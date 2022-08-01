<?php

namespace DataProvider\Reader;

use DataProvider\Entity\Token;

interface Reader
{
    public static function readTokenByName(string $name): ?Token;

    public static function findKey(string $key): bool;
}