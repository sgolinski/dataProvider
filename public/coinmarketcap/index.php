<?php

use DataProvider\Factory;
use DataProvider\Writer\RedisWriter;

require_once  'vendor/autoload.php';

header("Content-Type: text/plain");
$crawler = Factory::createCrawlerCmcService();
$alertService = Factory::createNotificationService();

try {
    $crawler->invoke();
} catch (Exception $exception) {
    $crawler->getClient()->restart();
}

$currentCoins = $crawler->getCoinsReadyForAlert();

if (empty($currentCoins)) {
    die('Nothing to show' . PHP_EOL);
}

$alertService->sendSlackMessage($currentCoins);
echo 'Downloading information about large movers from last hour ' . date('H:i:s') . PHP_EOL;
echo 'Start saving to Redis ' . date('H:i:s') . PHP_EOL;
RedisWriter::writeToRedis($currentCoins);
echo 'Finish saving to Redis ' . date('H:i:s') . PHP_EOL;

