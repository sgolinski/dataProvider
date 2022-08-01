<?php

namespace DataProvider\Entity;

use DataProvider\ValueObjects\Address;
use DataProvider\ValueObjects\Chain;
use DataProvider\ValueObjects\Name;
use DataProvider\ValueObjects\PercentageChange;
use DataProvider\ValueObjects\Url;

interface Token
{
    public function getName(): Name;

    public function getPercentageChange(): PercentageChange;

    public function getUrl(): Url;

    public function alert(): ?string;

    public function setPercentageChange(PercentageChange $dropPercent);

    public function setCreated();

    public function getCreated(): int;

    public function setAddress(Address $address);

    public function setChain(Chain $chain);

    public function getChain();

}