<?php

namespace DataProvider;

use DataProvider\Entity\Maker;
use DataProvider\Entity\Taker;
use DataProvider\Service\NotificationService;
use DataProvider\Service\CrawlerCmc;
use DataProvider\Service\CrawlerCoingecko;
use DataProvider\Service\CrawlerDexTracker;
use DataProvider\ValueObjects\Address;
use DataProvider\ValueObjects\Chain;
use DataProvider\ValueObjects\Currency;
use DataProvider\ValueObjects\PercentageChange;
use DataProvider\ValueObjects\Name;
use DataProvider\ValueObjects\Price;
use DataProvider\ValueObjects\Url;
use Facebook\WebDriver\WebDriverElement;
use Facebook\WebDriver\WebDriverSelect;
use Maknz\Slack\Client as SlackClient;
use Maknz\Slack\Message;

class Factory
{
    public static function createCrawlerCmcService(): CrawlerCmc
    {
        return new CrawlerCmc();
    }

    public static function createCrawlerCoingeckoService(): CrawlerCoingecko
    {
        return new CrawlerCoingecko();
    }

    public static function createCrawlerDexService(int $sites): CrawlerDexTracker
    {
        return new CrawlerDexTracker($sites);
    }

    public static function createNotificationService(): NotificationService
    {
        return new NotificationService();
    }


    public static function createSlackClient(string $hook): SlackClient
    {
        return new SlackClient($hook);
    }

    public static function createSlackMessage(): Message
    {
        return new Message();
    }

    public static function createTaker(Currency $tokenNameOfTaker, Price $price): Taker
    {
        return new Taker($tokenNameOfTaker, $price);
    }

    public static function createMakerBuilder(): MakerBuilder
    {
        return new MakerBuilder();
    }

    public static function createNotiAlert()
    {
    }

    /**
     * @throws \Facebook\WebDriver\Exception\UnexpectedTagNameException
     */
    public static function createWebDriverSelect(
        WebDriverElement $element
    ): WebDriverSelect
    {
        return new WebDriverSelect($element);
    }


}