<?php

namespace DataProvider\Entity;

use DataProvider\ValueObjects\Currency;
use DataProvider\ValueObjects\Name;
use DataProvider\ValueObjects\Price;
use InvalidArgumentException;

class Taker
{
    public Currency $token;

    public Price $dropValue;

    public const ALLOWED_PRICE_PER_TOKEN =
        [
            'wbnb' => 10.00,
            'cake' => 760.00,
            'bnb' => 10.00,
            'usdc' => 2470.00,
            'busd' => 2470.00,
            'usdt' => 2470.00,
            'fusdt' => 2470.00,
            'usdp' => 2470.00
        ];

    public function __construct(
        Currency $token,
        Price    $dropValue
    )
    {
        $this->ensureIsAllowedTakerToken($token);
        $this->ensureDropPriceIsHighEnough($token, $dropValue);
        $this->token = $token;
        $this->dropValue = $dropValue;
    }

    public function ensureIsAllowedTakerToken(Currency $token): void
    {
        if (!in_array($token->asString(), Name::$allowedTakerNames)) {
            throw new InvalidArgumentException('Currency not allowed');
        }
    }

    private function ensureDropPriceIsHighEnough(
        Currency $token,
        Price    $dropValue
    ): void
    {
        if ($dropValue->asFloat() < self::ALLOWED_PRICE_PER_TOKEN[$token->asString()]) {
            throw new InvalidArgumentException('Price is not high enough');
        }
    }

    public function getToken(): Currency
    {
        return $this->token;
    }

    public function getDropValue(): Price
    {
        return $this->dropValue;
    }

    public function updateDropValue(Price $dropValue): void
    {
        $this->dropValue = $dropValue;
    }

}