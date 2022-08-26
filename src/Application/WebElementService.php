<?php

namespace App\Application;

interface WebElementService
{
    public function transformElementsToTokens(array $savedWebElements);
}