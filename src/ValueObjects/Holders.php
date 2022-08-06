<?php

namespace DataProvider\ValueObjects;

class Holders
{
    public int $holders = 0;


    private function __construct(
        int $holders
    )
    {
        $this->holders = $holders;
    }


    public function asInt(): int
    {
        return $this->holders;
    }


}