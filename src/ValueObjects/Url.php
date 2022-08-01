<?php

namespace DataProvider\ValueObjects;

class Url
{
    private string $url;

    private function __construct(
        string $url
    )
    {
        $this->url =  $url;
    }

    public static function fromString(
        string $url
    ): self
    {
        return new self($url);
    }

    public function asString(): string
    {
        return $this->url;
    }


}