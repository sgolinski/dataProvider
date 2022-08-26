<?php
namespace App\Application\Validation\Coingecko;
class Selector
{
    public const TABLE = 'body > div.container >div:nth-child(7)> div:nth-child(2)';
    public const TBODY = '#gecko-table-all > tbody > tr:nth-child(-n+20)';

}