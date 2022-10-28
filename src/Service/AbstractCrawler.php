<?php

namespace DataProvider\Service;

use Symfony\Component\Panther\Client as PantherClient;

abstract class AbstractCrawler
{
    protected PantherClient $client;
    public string $url;
    protected ?string $script;

    public function __construct()
    {
        $this->client =PantherClient::createChromeClient();
    }

    public function getClient(): PantherClient
    {
        return $this->client;
    }

    protected function startClient(): void
    {
        echo "Start crawling " . date("F j, Y,  H:i:s") . PHP_EOL;
        $this->client->start();
        $this->client->get($this->url);
    }
}