<?php

namespace DataProvider\Entity;

use DataProvider\ValueObjects\Address;
use DataProvider\ValueObjects\Chain;
use DataProvider\ValueObjects\Holders;
use DataProvider\ValueObjects\Name;
use DataProvider\ValueObjects\PercentageChange;
use DataProvider\ValueObjects\Price;
use DataProvider\ValueObjects\Url;
use InvalidArgumentException;

class Maker implements Token
{
    public Name $name;
    public Address $address;
    public ?Holders $holders;
    public ?PercentageChange $percentageChange;
    public array $externalListingLinks;
    public int $created;
    public Url $url;
    public Price $price;
    public Chain $chain;


    public function alert(): string
    {
        return "Name: " . $this->getName()->asString() . PHP_EOL .
            "Drop percent: -" . str_replace("-", "", (string)$this->getPercentageChange()->asFloat(),) . '%' . PHP_EOL .
            "Listing: " . $this->getUrl()->asString() . PHP_EOL .
            "Poocoin:  https://poocoin.app/tokens/" . $this->getAddress()->asString() . PHP_EOL .
            'Token Sniffer: https://tokensniffer.com/token/' . $this->getAddress()->asString() . PHP_EOL .
            'Chain: ' . $this->getChain()->asString() . PHP_EOL;
    }

    public function getAddress(): Address
    {
        return $this->address;
    }

    public function getName(): Name
    {
        return $this->name;
    }

    public function setHolders(Holders $holders)
    {
        $this->holders = $holders;
    }

    public function getHolders(): Holders
    {
        return $this->holders;
    }

    public function getTaker(): Taker
    {
        return $this->taker;
    }

    public function getExternalListingByIndex(
        string $index
    ): string
    {
        return $this->externalListingLinks[$index];
    }

    public function getCreated(): int
    {
        return $this->created;
    }

    public function getUrl(): Url
    {
        return $this->url;
    }

    public function setCreated()
    {
        $this->created = time();
    }

    public function setAddress(Address $address)
    {
        $this->address = $address;
    }

    public function setChain(Chain $chain): void
    {
        $this->chain = $chain;
    }

    public function getPercentageChange(): PercentageChange
    {
        return $this->percentageChange;
    }

    public function setPercentageChange(PercentageChange $percentageChange)
    {
        $this->percentageChange = $percentageChange;
    }

    public function setName(Name $name)
    {
        $this->name = $name;
    }

    public function setTaker(Taker $taker)
    {
        $this->taker = $taker;
    }

    public function setPrice(Price $price): void
    {
        $this->price = $price;
    }

    public function setUrl(Url $url)
    {
        $this->url = $url;
    }

    public function getChain(): Chain
    {
        return $this->chain;
    }
}