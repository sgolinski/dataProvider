<?php

namespace DataProvider;

use DataProvider\Service\NotificationService;
use DataProvider\Service\CrawlerCmc;
use DataProvider\Service\CrawlerCoingecko;
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

    public static function createMakerBuilder(): MakerBuilder
    {
        return new MakerBuilder();
    }

    public static function createWebDriverSelect(
        WebDriverElement $element
    ): WebDriverSelect
    {
        return new WebDriverSelect($element);
    }

}