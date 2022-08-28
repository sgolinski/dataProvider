<?php

namespace App\Application;

use ArrayIterator;
use Exception;
use Facebook\WebDriver\Remote\RemoteWebElement;
use Facebook\WebDriver\WebDriverBy;
use Symfony\Component\Panther\Client;


class CoinmarketcapService implements PantherService
{

    public function getContent()
    {

        $list = $this->client->getCrawler()
            ->filter(SelectorJs::TABLE)
            ->filter(SelectorJs::TBODY)
            ->children()
            ->getIterator();
    }

    public function createTokensFromContent(
        ArrayIterator $content
    )
    {
        foreach ($content as $webElement) {
            assert($webElement instanceof RemoteWebElement);

            $percent = (float)$webElement
                ->findElement(WebDriverBy::cssSelector(SelectorJs::PERCENT))
                ->getText();
            $percent = PercentageChange::fromFloat((float)$percent);
            if ($percent->asFloat() < 19.0) {
                continue;
            }

            $name = $webElement
                ->findElement(WebDriverBy::tagName('a'))
                ->findElement(WebDriverBy::tagName('p'))
                ->getText();
            $name = Name::fromString($name);
            $token = RedisReader::findKey($name->asString());

            if ($token) {
                continue;
            }

            $url = $webElement
                ->findElement(WebDriverBy::tagName('a'))
                ->getAttribute('href');
            $url = Url::fromString('https://coinmarketcap.com' . $url);

            $price = $webElement
                ->findElement(WebDriverBy::cssSelector(''))
                ->getText();
            $price = Price::fromFloat((float)$price);

            $currentScrappedTokens[] = Factory::createMakerBuilder()
                ->setName($name)
                ->setPrice($price)
                ->setPercentageChange($percent)
                ->setUrl($url)
                ->setCreated()
                ->build();
        }
    }

    public function getOneToken($currentScrappedTokens): array
    {
        echo 'Start assigning chain and address ' . date('H:i:s', time()) . PHP_EOL;
        $returnCoins = [];
        foreach ($currentScrappedTokens as $token) {
            try {
                assert($token instanceof Token);

                $this->client->refreshCrawler();
                $this->client
                    ->get($token->getUrl()->asString());

                $cont = $this->client->getCrawler()
                    ->filter('div.content')
                    ->filter('a.cmc-link')
                    ->getAttribute('href');

                if (empty($cont)) {
                    continue;
                }
                if (str_contains($cont, 'bsc')) {
                    $chain = Chain::fromString('bsc');
                    $address = Address::fromString(str_replace('https://bscscan.com/token/', '', $cont));
                    $token = Factory::createMakerBuilder()->setMaker($token);
                    $returnCoins[] = $token->setAddress($address)->setChain($chain)->build();
                }
            } catch
            (Exception $exception) {
                continue;
            }
        }

        echo 'Finish assigning chain and address ' . date('H:i:s', time()) . PHP_EOL;
        return $returnCoins;
    }

    public function saveWebElements(Url $url): void
    {
        // TODO: Implement saveWebElements() method.
    }

    public function findOneElementOn(Url $url): string
    {
        // TODO: Implement findOneElementOn() method.
    }

    public function refreshClient(Url $url): void
    {
        // TODO: Implement refreshClient() method.
    }

    public function ensureIsNotBusy(Url $url): void
    {
        // TODO: Implement ensureIsNotBusy() method.
    }

    public function getClient(): Client
    {
        // TODO: Implement getClient() method.
    }

    public function savedWebElements(): array
    {
        // TODO: Implement savedWebElements() method.
    }
}