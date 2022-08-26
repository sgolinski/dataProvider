<?php

namespace App\Application;

use App\Application\Validation\Coinmarketcap\SelectorJs;
use App\Domain\ValueObjects\Chain;
use App\Domain\ValueObjects\Id;
use App\Domain\ValueObjects\Url;
use Symfony\Component\Panther\Client;

class CoingeckoService implements PantherService
{

    private array $elements = [];
    private Client $client;


    public function __construct()
    {
        $this->client = Client::createFirefoxClient();
    }

    public function saveWebElements(Url $url): void
    {
        $this->refreshClient($url);
        $this->elements = $this->client->getCrawler()
            ->filter(SelectorJs::TABLE)
            ->filter(SelectorJs::TBODY)
            ->getIterator()->getArrayCopy();
    }

    public function findChainAndAddressOn(Url $url): array
    {
        $this->client->get($url->asString());
        $this->client->refreshCrawler();
        $chain = $this->findChainIn();
        $id = $this->findIdIn();
        return [
            'id' => $id,
            'chain' => $chain
        ];
    }

    public function refreshClient(Url $url): void
    {
        usleep(30000);
        $this->client->start();
        usleep(30000);
        $this->client->get($url->asString());
        usleep(30000);
        $this->client->refreshCrawler();
        usleep(30000);
    }


    public function getClient(): Client
    {
        return $this->client;
    }


    private function findChainIn(): ?Chain
    {
        $chain = $this->client->getCrawler()
            ->filter('div.coin-link-row.tw-mb-0 > div > div > img ')
            ->getAttribute('data-chain-id');

        if ($chain === '56') {
            return Chain::fromString('bsc');
        }

        return null;
    }

    private function findIdIn(): ?Id
    {
        $address = $this->client->getCrawler()
            ->filter('div.coin-link-row.tw-mb-0 > div > div > img ')
            ->getAttribute('data-address');

        if ($address !== null) {
            $address = trim(str_replace('/address/', '', $address));
            return Id::fromString($address);
        }
        return null;
    }

    public function savedWebElements(): array
    {
        return $this->elements;
    }

}