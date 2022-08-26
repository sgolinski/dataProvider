<?php

namespace App\Application;


class CoingeckoApplication
{
    private Application $application;

    public function __construct()
    {
        $this->application = new Application(new CoingeckoService(), new CoingeckoWebElementService());
    }

    public function invoke()
    {
        $this->application->importAllTransactionsFrom(new ImportTokensFromCoingecko());
        $this->application->fillAllNotCompletedTokens(new  FindAllNotCompleteCoingeckoTokens());
    }
}