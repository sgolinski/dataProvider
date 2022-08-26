<?php

namespace App\Domain\Coingecko;

use App\Domain\ValueObjects\Chain;
use App\Domain\ValueObjects\Id;
use App\Domain\ValueObjects\Name;
use App\Domain\ValueObjects\Percentage;
use App\Domain\ValueObjects\Price;
use App\Domain\ValueObjects\Url;


class Token
{
    public Id $id;
    public Name $name;
    public Url $url;
    public Price $price;
    public Chain $chain;
    public Percentage $percent;

    public function __construct($name, $percent, $price, $url)
    {
        $this->name = $name;
        $this->percent = $percent;
        $this->price = $price;
        $this->url = $url;
    }

    public static function fromParams(
        Name       $name,
        Percentage $percent,
        Price      $price,
        Url        $url
    ): self
    {
        return new self($name, $percent, $price, $url);
    }

    public static function writeNewFrom(
        Token $notCompleteToken,
         array     $missingProperties
    ): self
    {
        $token = new Token(
            $notCompleteToken->name,
            $notCompleteToken->percent,
            $notCompleteToken->price,
            $notCompleteToken->url);
        $token->id = Id::fromString($missingProperties['id']);
        $token->chain = Chain::fromString($missingProperties['chain']);

        return $token;
    }

    public function name(): Name
    {
        return $this->name;
    }

}