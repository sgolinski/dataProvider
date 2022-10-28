<?php

namespace DataProvider;

use DataProvider\Entity\Maker;
use DataProvider\ValueObjects\Address;
use DataProvider\ValueObjects\Chain;
use DataProvider\ValueObjects\Name;
use DataProvider\ValueObjects\PercentageChange;
use DataProvider\ValueObjects\Price;
use DataProvider\ValueObjects\Url;

class MakerBuilder
{
    private Maker $maker;

    public function setMaker(Maker $maker): self
    {
        $this->maker = $maker;
        return $this;
    }

    public function __construct()
    {
        $this->reset();
    }

    public function reset(): void
    {
        $this->maker = new Maker();
    }

    public function setName(Name $name): self
    {
        $this->maker->setName($name);

        return $this;
    }

    public function setAddress(Address $address): self
    {
        $this->maker->setAddress($address);
        return $this;
    }

    public function build(): Maker
    {
        $maker = $this->maker;

        $this->reset();

        return $maker;
    }

    public function setPrice(Price $price): self
    {
        $this->maker->setPrice($price);
        return $this;
    }

    public function setCreated(): self
    {
        $this->maker->setCreated();
        return $this;
    }

    public function setPercentageChange(PercentageChange $percentageChange): self
    {
        $this->maker->setPercentageChange($percentageChange);
        return $this;
    }

    public function setUrl(Url $url): self
    {
        $this->maker->setUrl($url);
        return $this;
    }

    public function setChain(Chain $chain): self
    {
        $this->maker->setChain($chain);
        return $this;
    }
}