<?php

namespace App\Domain;

use App\Domain\ValueObjects\Chain;
use App\Domain\ValueObjects\Id;
use App\Domain\ValueObjects\Name;
use App\Domain\ValueObjects\Price;
use App\Domain\ValueObjects\Url;


class Token
{
    public Id $id;
    public Name $name;
    public Url $url;
    public Price $price;
    public Chain $chain;

    public function __construct(Id $id)
    {
        $this->id = $id;
    }

}