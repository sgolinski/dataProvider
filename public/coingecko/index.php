<?php

use DataProvider\Factory;
use DataProvider\ValueObjects\Url;
use DataProvider\Writer\RedisWriter;

require_once  '/mnt/app/vendor/autoload.php';

header("Content-Type: text/plain");

$url = Url::fromString('https://www.coingecko.com/en/crypto-gainers-losers?time=h1');
$crawler = Factory::createCrawlerCoingeckoService();
$cmc = Factory::createNotificationService();

try {
    $crawler->invoke();
} catch (Exception $exception) {
    $crawler->getClient()->restart();
}
$currentCoins = $crawler->getCoinsReadyForAlert();

if (empty($currentCoins)) {
    die('Nothing to show' . PHP_EOL);
}

$cmc->sendSlackMessage($currentCoins);
echo 'Downloading information about large movers from last hour ' . date('H:i:s') . PHP_EOL;
echo 'Start saving to Redis ' . date('H:i:s') . PHP_EOL;
RedisWriter::writeToRedis($currentCoins);
echo 'Finish saving to Redis ' . date('H:i:s') . PHP_EOL;

