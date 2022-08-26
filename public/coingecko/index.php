<?php

use App\Application\CoingeckoApplication;

require_once './vendor/autoload.php';

header("Content-Type: text/plain");

$application = new CoingeckoApplication();

$application->invoke();