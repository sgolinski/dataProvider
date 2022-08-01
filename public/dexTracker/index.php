<?php

use DataProvider\Entity\Token;
use DataProvider\Factory;
use DataProvider\Reader\RedisReader;
use DataProvider\ValueObjects\Name;

require_once '/mnt/app/vendor/autoload.php';

header("Content-Type: text/plain");

$sites = (int)$argv[1];
$repeats = (int)$argv[2];

$crawler = Factory::createCrawlerDexService($sites);

$crawler->invoke();

echo 'Cronjob finished ' . date('H:i:s') . PHP_EOL;

$potentialDrop = array_count_values($crawler->getNamesToFindDrop());

$returnDrop = [];

foreach ($potentialDrop as $key => $value) {
    $key = strtolower(trim($key));
    if (!in_array($key, Name::$blackListedCoins) && !in_array($key, Name::$allowedTakerNames)) {
        if ($repeats < (int)$value) {
            if (RedisReader::findKey($key)) {
                $maker = RedisReader::readTokenByName($key);
                assert($maker instanceof Token);
                $returnDrop[] = $maker->alert();
            } else {
                $returnDrop[] = $key . PHP_EOL;
            }
        }
    }
}
if (!empty($returnDrop)) {
  Factory::createNotificationService()->sendSlackMessage($returnDrop, true);
}
