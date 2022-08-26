<?php

namespace App\Application;

use App\Domain\Coingecko\Token;
use App\Infrastructure\InMemoryRepository;

class Application
{
    private PantherService $pantherService;
    private WebElementService $service;
    private InMemoryRepository $inMemoryRepository;
    private NotificationService $NotificationsService;

    public function __construct(PantherService $pantherService, WebElementService $service)
    {
        $this->pantherService = $pantherService;
        $this->service = $service;
        $this->inMemoryRepository = new InMemoryRepository();
    }

    public function importAllTransactionsFrom(ImportTokensCommand $command): void
    {
        $this->importTokens($command);
    }

    private function importTokens(ImportTokensCommand $command): void
    {
        $this->pantherService->saveWebElements($command->url());

        $this->service->transformElementsToTokens($this->pantherService->savedWebElements());
    }

    public function fillAllNotCompletedTokens(FindAllNotComplete $command): void
    {
        $this->completeTokens($command);
    }

    private function completeTokens(FindAllNotComplete $command): void
    {
        $notCompleteTokens = $this->inMemoryRepository->findAll($command);
        var_dump($notCompleteTokens);
        foreach ($notCompleteTokens as $notCompleteToken) {
            $missingProperties = $this->pantherService->findChainAndAddressOn($notCompleteToken->url());
            $token = Token::writeNewFrom($notCompleteToken, $missingProperties);
            $this->NotificationsService->sendNotfication($token);
        }
    }
}