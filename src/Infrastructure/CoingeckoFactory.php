<?php

namespace App\Infrastructure;


use App\Domain\Coingecko\Token;
use App\Domain\ValueObjects\Name;
use App\Domain\ValueObjects\Percentage;
use App\Domain\ValueObjects\Price;
use App\Domain\ValueObjects\Url;
use Facebook\WebDriver\Remote\RemoteWebElement;
use Facebook\WebDriver\WebDriverBy;

class CoingeckoFactory
{

    public function createTokenFrom(RemoteWebElement $webElement)
    {
        $name = $this->createNameFrom($webElement);
        $percent = $this->createPercentageFrom($webElement);
        $price = $this->createPriceFrom($webElement);
        $url = $this->createUrlFrom($webElement);

        return Token::fromParams($name, $percent, $price, $url);
    }

    private function createPercentageFrom(RemoteWebElement $webElement): Percentage
    {
        $percent = $webElement
            ->findElement(WebDriverBy::cssSelector('td:nth-child(4)'))
            ->getText();
        return Percentage::fromFloat((float)$percent);

    }

    private function createPriceFrom(RemoteWebElement $webElement): Price
    {
        $price = $webElement
            ->findElement(WebDriverBy::cssSelector('td:nth-child(3)'))
            ->getText();

        $price = str_replace('$', '', $price);
        return Price::fromFloat((float)$price);
    }

    private function createUrlFrom(RemoteWebElement $webElement): Url
    {
        $url = $webElement
            ->findElement(WebDriverBy::cssSelector('td:nth-child(2)'))
            ->findElement(WebDriverBy::tagName('a'))
            ->getAttribute('href');

        return Url::fromString('https://www.coingecko.com' . $url);

    }

    private function createNameFrom(RemoteWebElement $webElement): Name
    {
        $name = $webElement
            ->findElement(WebDriverBy::cssSelector('td:nth-child(1)'))
            ->findElement(WebDriverBy::tagName('div'))
            ->findElement(WebDriverBy::cssSelector('div:nth-child(1)'))
            ->getText();


        return Name::fromString($name);
    }
}