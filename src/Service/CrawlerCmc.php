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

class CrawlerCmc extends AbstractCrawler implements Crawler
{

    private array $coinsReadyForAlert = [];

    public string $url = 'https://coinmarketcap.com/gainers-losers/';

    private const SCRIPT = <<<EOF
// get all DIV elements
var items = document.querySelectorAll('div');
var firstClickDiv = false;

for(const item of items) {
    // find the first div that contains the text 24h
    if (item.innerText == "24h") {
        firstClickDiv = item;
        break;   
    }

}
// click this div to show up the dropdown
firstClickDiv.click();
var dropdown = firstClickDiv.nextSibling;
dropdown.querySelector("button").click();

var secondClickDiv = false;
for(const item of items) {
    // find the first div that contains the text 24h
    if (item.innerText == "Top 100") {
        secondClickDiv = item;   
        break;
    }

}
secondClickDiv.click();
var secondDropdown = secondClickDiv.nextSibling;
secondDropdown.querySelector("button:nth-of-type(3)").click();
EOF;


    public function invoke(): void
    {
        try {
            $this->startClient();
            $this->client->executeScript(self::SCRIPT);
            sleep(1);
            $this->client->refreshCrawler();
            $content = $this->getContent();
            $currentRoundCoins = $this->createTokensFromContent($content);
            $this->coinsReadyForAlert = $this->assignChainAndAddress($currentRoundCoins);
        } catch (Exception $exception) {
            $this->client->restart();
            echo $exception->getFile() . ' ' . $exception->getLine() . PHP_EOL;
        } finally {
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
                ->filter('div.sc-1yw69nc-0.DaVcG.table-wrap > div > div:nth-child(2)')
                ->filter('table.h7vnx2-2.cZkmip.cmc-table > tbody')
                ->children()
                ->getIterator();

        } catch (Exception $exception) {
            echo $exception->getMessage();
        }
        echo 'Content downloaded ' . date('H:i:s', time()) . PHP_EOL;
        return $list;
    }

    public function createTokensFromContent(
        ArrayIterator $content
    ): array
    {
        echo 'Start creating tokens from content ' . date('H:i:s', time()) . PHP_EOL;
        $currentScrappedTokens = [];
        foreach ($content as $webElement) {
            assert($webElement instanceof RemoteWebElement);

            try {
                $percent = (float)$webElement
                    ->findElement(WebDriverBy::cssSelector('td:nth-child(4)'))
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
                    ->findElement(WebDriverBy::cssSelector('td:nth-child(3)'))
                    ->getText();
                $price = Price::fromFloat((float)$price);

                $currentScrappedTokens[] = Factory::createMakerBuilder()
                    ->setName($name)
                    ->setPrice($price)
                    ->setPercentageChange($percent)
                    ->setUrl($url)
                    ->setCreated()
                    ->build();

            } catch (Exception $e) {
                echo 'Error when crawl information ' . $e->getMessage() . PHP_EOL;
                continue;
            }
        }
        echo 'Finish creating tokens from content ' . date('H:i:s', time()) . PHP_EOL;
        return $currentScrappedTokens;
    }

    public function assignChainAndAddress($currentScrappedTokens): array
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
                    $address = trim(str_replace('/address/', '', $cont));
                    $address = Address::fromString($cont);
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

    /**
     * @return array
     */
    public function getCoinsReadyForAlert(): array
    {
        return $this->coinsReadyForAlert;
    }
}