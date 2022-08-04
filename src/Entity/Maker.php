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
    public ?Taker $taker = null;
    public ?PercentageChange $percentageChange;
    public array $externalListingLinks;
    public int $created;
    public Url $url;
    public Price $price;
    public Chain $chain;

    public function ensureTokenNameIsNotBlacklisted(
        string $name
    ): void
    {
        if (in_array($name, NAME::$blackListedCoins)) {
            throw new InvalidArgumentException('Currency is on the blacklist');
        }
    }

    public function setLinkToListings(): void
    {
        $this->externalListingLinks = [
            'cmc' => 'https://coinmarketcap.com/currencies/' . $this->name->asString(),
            'coingecko' => 'https://www.coingecko.com/en/coins/' . $this->address->asString(),
            'poocoin' => 'https://poocoin.app/tokens/' . $this->address->asString(),
        ];
    }

    public function alertWithTaker(): string
    {
        return PHP_EOL . PHP_EOL . "Tracker with redis \nName: " . $this->name->asString() . PHP_EOL .
            "Drop value: -" . $this->getTaker()->getDropValue()->asFloat() . ' ' . $this->getTaker()->getToken()->asString() . PHP_EOL .
            "Cmc: " . $this->getExternalListingByIndex('cmc') . PHP_EOL .
            "Coingecko: " . $this->getExternalListingByIndex('coingecko') . PHP_EOL .
            "Poocoin: " . $this->getExternalListingByIndex('poocoin') . PHP_EOL;
    }

    public function alertWithoutTaker(): string
    {
        return "Name: " . $this->getName()->asString() . PHP_EOL .
            "Drop percent: -" . str_replace("-", "", (string)$this->getPercentageChange()->asFloat(),) . '%' . PHP_EOL .
            "Listing: " . $this->getUrl()->asString() . PHP_EOL .
            "Poocoin:  https://poocoin.app/tokens/" . $this->getAddress()->asString() . PHP_EOL .
            'Chain: ' . $this->getChain()->asString() . PHP_EOL;
    }

    public function alert(): string
    {
        return $this->taker !== null ? $this->alertWithTaker() : $this->alertWithoutTaker();
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