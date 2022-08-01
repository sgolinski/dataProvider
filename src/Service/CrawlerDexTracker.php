<?php

namespace DataProvider\Service;

use ArrayIterator;
use DataProvider\Entity\Token;
use DataProvider\Factory;
use DataProvider\Reader\RedisReader;
use DataProvider\ValueObjects\Address;
use DataProvider\ValueObjects\Holders;
use DataProvider\ValueObjects\Name;
use DataProvider\Writer\RedisWriter;
use Exception;
use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Exception\UnexpectedTagNameException;
use Facebook\WebDriver\Remote\RemoteWebElement;
use Facebook\WebDriver\WebDriverBy;
use InvalidArgumentException;
use Symfony\Component\Panther\Client as PantherClient;


class CrawlerDexTracker extends AbstractCrawler implements Crawler
{

    public array $namesToFindDrop = [];

    private const URL_TOKEN = 'https://bscscan.com/token/';

    private const URL = 'https://bscscan.com/dextracker?filter=1';
    private int $sites;
    private const SCRIPT = <<<EOF
var selectedA = document.querySelector('#selectDex');
var divWithDexList = document.querySelector('#selectDexButton');
var select = document.getElementById('ContentPlaceHolder1_ddlRecordsPerPage');

selectedA.click();
divWithDexList.querySelector('#selectDexButton > a:nth-child(5) > img').click();
EOF;

    public function __construct(int $sites)
    {
        $this->sites = $sites;
    }

    private const INDEX_OF_SHOWN_ROWS = 3;

    public function invoke(): void
    {
        try {
            echo "Start crawling " . date("F j, Y, g:i:s a") . PHP_EOL;
            $this->getCrawlerForWebsite(self::URL);
            $this->client->executeScript(self::SCRIPT);
            $this->changeOnWebsiteToShowMoreRecords();
            sleep(1);
            $this->scrappingData();
            $this->client->restart();
        } catch (Exception $exception) {
            echo $exception->getMessage() . PHP_EOL;
            $this->client->restart();
        } finally {
            $this->client->close();
            $this->client->quit();
        }
    }

    public function getContent(): ?ArrayIterator
    {
        try {
            $list = $this->client->getCrawler()
                ->filter('#content > div.container.space-bottom-2 > div > div.card-body')
                ->filter('table.table-hover > tbody')
                ->children()
                ->getIterator();

        } catch (Exception $exception) {
            echo $exception->getMessage();
        }
        return $list;
    }

    public function assignMakerAndTakerFrom(
        ?ArrayIterator $content
    ): array
    {
        $tokensWithoutHolders = [];
        foreach ($content as $webElement) {
            try {
                assert($webElement instanceof RemoteWebElement);

                $name = $webElement
                    ->findElement(WebDriverBy::cssSelector('tr > td:nth-child(3) > a'))
                    ->getText();

                $this->namesToFindDrop[] = $name;

                $nameOfMaker = Name::fromString($name);
                $this->ensureTokenNameIsNotBlacklisted($nameOfMaker->asString());

                $maker = RedisReader::findKey($nameOfMaker->asString());

                if ($maker) {
                    continue;
                }

                $information = $webElement
                    ->findElement(WebDriverBy::cssSelector('tr > td:nth-child(5)'))
                    ->getText();
                $service = Information::fromString($information);
                $tokenNameOfTaker = $service->getToken();

                $price = $service->getPrice();
                $taker = Factory::createTaker($tokenNameOfTaker, $price);

                $address = $webElement
                    ->findElement(WebDriverBy::cssSelector('tr > td:nth-child(3) > a'))
                    ->getAttribute('href');

                $address = Address::fromString($address);

                $token = Factory::createMakerBuilder()
                    ->setName($nameOfMaker)
                    ->setAddress($address)
                    ->setTaker($taker)
                    ->setCreated()
                    ->build();

                $tokensWithoutHolders[] = $token;

            } catch (InvalidArgumentException) {
                continue;
            }
        }

        return $tokensWithoutHolders;
    }

    public function proveIfIsWorthToBuyIt($makersWithoutHolders): ?array
    {

        if ($makersWithoutHolders !== null) {
            $tokensForAlert = [];

            foreach ($makersWithoutHolders as $maker) {

                assert($maker instanceof Token);

                $url = self::URL_TOKEN . $maker->getAddress()->asString();

                $this->getCrawlerForWebsite($url);

                $holdersString = $this->client->getCrawler()
                    ->filter('#ContentPlaceHolder1_tr_tokenHolders > div > div.col-md-8 > div > div')
                    ->getText();

                try {
                    $holdersNumber = (int)str_replace(',', "", explode(' ', $holdersString)[0]);
                    $holders = Holders::fromInt($holdersNumber);
                    $maker->setHolders($holders);

                    $tokensForAlert[] = $maker;
                } catch (Exception) {
                    continue;
                }
            }
            return $tokensForAlert;

        } else {
            return null;
        }
    }

    private function scrappingData(): void
    {
        $tokensWithoutHolders = [];
        echo 'Start getting content ' . date("F j, Y, g:i:s a") . PHP_EOL;
        for ($i = 0; $i < $this->sites; $i++) {
            $this->client->refreshCrawler();
            $data = $this->getContent();
            $tokensWithoutHolders[] = $this->assignMakerAndTakerFrom($data);
            $nextPage = $this->client
                ->findElement(WebDriverBy::cssSelector('#ctl00 > div.d-md-flex.justify-content-between.my-3 > ul > li:nth-child(4) > a'));
            usleep(3000);
            $nextPage->click();
            $this->client->refreshCrawler();
        }
        echo 'Finish getting content ' . date("F j, Y, g:i:s a") . PHP_EOL;;
        echo 'Start assigning holders ' . date("F j, Y, g:i:s a") . PHP_EOL;

        foreach ($tokensWithoutHolders as $packet) {
            $tokensReadyForAlert = $this->proveIfIsWorthToBuyIt($packet);
            if ($tokensReadyForAlert) {
                Factory::createNotificationService()->sendMessage($packet);
                RedisWriter::writeToRedis($packet);
            }
        }
        echo 'Finish assigning holders ' . date("F j, Y, g:i:s a") . PHP_EOL;

    }

    private function changeOnWebsiteToShowMoreRecords(): void
    {
        try {
            $selectRows = $this->client->findElement(WebDriverBy::id('ContentPlaceHolder1_ddlRecordsPerPage'));
            usleep(30000);
            $webDriverSelect = Factory::createWebDriverSelect($selectRows);
            $webDriverSelect->selectByIndex(self::INDEX_OF_SHOWN_ROWS);
            usleep(30000);
        } catch (NoSuchElementException $exception) {
            echo $exception->getMessage();
        } catch (UnexpectedTagNameException $e) {
            echo $e->getMessage();
        }
    }


    private function getCrawlerForWebsite(
        string $url
    ): void
    {
        $this->client = PantherClient::createChromeClient();
        $this->client->start();
        $this->client->get($url);
        usleep(30000);
        $this->client->refreshCrawler();
        usleep(30000);
    }

    public function getNamesToFindDrop(): array
    {
        return $this->namesToFindDrop;
    }

    public function ensureTokenNameIsNotBlacklisted(
        string $name
    ): void
    {
        if (in_array($name, NAME::$blackListedCoins)) {
            throw new InvalidArgumentException('Currency is on the blacklist');
        }
    }
}