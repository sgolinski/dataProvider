<?php

namespace App\Application;

use App\Domain\ValueObjects\Url;

interface ImportTokensCommand
{
    public function url(): Url;
}