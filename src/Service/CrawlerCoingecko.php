<?php

namespace DataProvider\Service;

use ArrayIterator;
use DataProvider\Entity\Token;
use DataProvider\Factory;
use DataProvider\Reader\RedisReader;
use DataProvider\ValueObjects\Address;
use DataProvider\ValueObjects\Chain;
use DataProvider\ValueObjects\Name;
use DataProvider\ValueObjects\PercentageChange;
use DataProvider\ValueObjects\Price;
use DataProvider\ValueObjects\Url;
use Exception;
use Facebook\WebDriver\Remote\RemoteWebElement;
use Facebook\WebDriver\WebDriverBy;

class CrawlerCoingecko extends AbstractCrawler implements Crawler
{

    public string $url = 'https://www.coingecko.com/de/crypto-gainers-losers?time=h1&top=all';

    private array $coinsReadyForAlert = [];

    public function invoke(): void
    {
        try {
            $this->startClient();
            $content = $this->getContent();
            $currentRoundCoins = $this->createTokensFromContent($content);
            $this->coinsReadyForAlert = $this->assignChainAndAddress($currentRoundCoins);

        } catch (Exception $exception) {
            $this->client->close();
            $this->client->quit();
        }
    }

    public function getContent(): ?ArrayIterator
    {
        echo 'Start getting content ' . date('H:i:s', time()) . PHP_EOL;
        $list = null;

        try {
            $list = $this->client->getCrawler()
                ->filter('body > div.container >div:nth-child(8)> div:nth-child(2)')
                ->filter('#gecko-table-all > tbody > tr:nth-child(-n+20)')
                ->getIterator();

        } catch (Exception $exception) {
            echo $exception->getMessage();
        }

        echo 'Content downloaded ' . date('H:i:s', time()) . PHP_EOL;
        return $list;
    }

    private function createTokensFromContent(
        ArrayIterator $content
    ): array
    {
        echo 'Start creating tokens from content ' . date('H:i:s', time()) . PHP_EOL;
        $currentScrappedTokens = [];


        foreach ($content as $webElement) {
            try {
                assert($webElement instanceof RemoteWebElement);

                $percent = $webElement
                    ->findElement(WebDriverBy::cssSelector('td:nth-child(4)'))
                    ->getText();
                $percent = PercentageChange::fromFloat((float)$percent);

                if ($percent->asFloat() > -19.0) {
                    continue;
                }

                $name = $webElement
                    ->findElement(WebDriverBy::cssSelector('td:nth-child(1)'))
                    ->findElement(WebDriverBy::tagName('div'))
                    ->findElement(WebDriverBy::cssSelector('div:nth-child(1)'))
                    ->getText();

                $name = Name::fromString($name);

                $token = RedisReader::findKey($name->asString());

                if ($token) {
                    continue;
                } else {

                    $url = $webElement
                        ->findElement(WebDriverBy::cssSelector('td:nth-child(2)'))
                        ->findElement(WebDriverBy::tagName('a'))
                        ->getAttribute('href');
                    $url = Url::fromString('https://www.coingecko.com' . $url);

                    $price = $webElement
                        ->findElement(WebDriverBy::cssSelector('td:nth-child(3)'))
                        ->getText();

                    $price = str_replace('$', '', $price);
                    $price = Price::fromFloat((float)$price);

                    $currentScrappedTokens[] = Factory::createMakerBuilder()
                        ->setName($name)
                        ->setPrice($price)
                        ->setPercentageChange($percent)
                        ->setUrl($url)
                        ->setCreated()
                        ->build();
                }

            } catch
            (Exception $e) {
                echo 'Error when crawl information about Token ' . $e->getMessage() . PHP_EOL;
                continue;
            }
        }
        echo 'Finish creating tokens from content ' . date('H:i:s', time()) . PHP_EOL;

        return $currentScrappedTokens;
    }

    private function assignChainAndAddress($currentScrappedTokens): array
    {
        echo 'Start assigning chain and address ' . date('H:i:s', time()) . PHP_EOL;
        $returnCoins = [];
        foreach ($currentScrappedTokens as $token) {

            try {
                assert($token instanceof Token);

                $this->client->get($token->getUrl()->asString());
                $this->client->refreshCrawler();

                $chain = $this->client->getCrawler()
                    ->filter('div.coin-link-row.tw-mb-0 > div > div > img ')
                    ->getAttribute('data-chain-id');
                if ($chain !== '56') {
                    continue;
                }
                $chain = Chain::fromString('bsc');
                $address = $this->client->getCrawler()
                    ->filter('div.coin-link-row.tw-mb-0 > div > div > img ')
                    ->getAttribute('data-address');

                if ($address === null) {
                    continue;
                }
                $address = trim(str_replace('/address/', '', $address));
                $address = Address::fromString($address);
                $returnCoins[] = Factory::createMakerBuilder()->setMaker($token)->setAddress($address)->setChain($chain)->build();

            } catch
            (Exception $exception) {
                continue;
            }
        }
        echo 'Finish assigning chain and address ' . date('H:i:s', time()) . PHP_EOL;
        return $returnCoins;
    }

    public function getCoinsReadyForAlert(): array
    {
        return $this->coinsReadyForAlert;
    }

}