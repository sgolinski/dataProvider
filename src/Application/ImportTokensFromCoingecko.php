<?php

namespace App\Application;

use App\Application\Validation\Coingecko\Url as URI;
use App\Domain\ValueObjects\Url;

class ImportTokensFromCoingecko implements ImportTokensCommand
{
    private Url $url;

    public function __construct()
    {
        $this->url = Url::fromString(URI::URL);
    }

    public function url(): Url
    {
        return $this->url;
    }
}