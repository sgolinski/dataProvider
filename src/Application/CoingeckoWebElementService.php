<?php

namespace App\Application;

use App\Infrastructure\CoingeckoFactory;
use App\Infrastructure\InMemoryRepository;


class CoingeckoWebElementService implements WebElementService
{

    protected CoingeckoFactory $tokenFactory;
    private InMemoryRepository $inMemoryRepository;

    public function __construct()
    {
        $this->tokenFactory = new CoingeckoFactory();
        $this->inMemoryRepository = new InMemoryRepository();
    }

    public function transformElementsToTokens(array $savedWebElements)
    {
        foreach ($savedWebElements as $webElement) {
            $token = $this->tokenFactory->createTokenFrom($webElement);
            $this->inMemoryRepository->add($token);
        }
    }
}