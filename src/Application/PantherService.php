<?php

namespace App\Application;

use App\Domain\ValueObjects\Url;
use Symfony\Component\Panther\Client;

interface PantherService
{
    public function saveWebElements(Url $url): void;

    public function refreshClient(Url $url): void;

    public function getClient(): Client;

    public function savedWebElements(): array;
}