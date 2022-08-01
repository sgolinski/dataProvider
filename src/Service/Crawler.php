<?php

namespace DataProvider\Service;

use ArrayIterator;

interface Crawler
{
    public function invoke(): void;

    public function getContent(): ?ArrayIterator;

}