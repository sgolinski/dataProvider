<?php

namespace App\Application\Validation\Coinmarketcap;
class SelectorJs
{
    public const Script = <<<EOF
// get all DIV elements
var items = document.querySelectorAll('div');
var firstClickDiv = false;

for(const item of items) {
    // find the first div that contains the text 24h
    if (item.innerText == "24h") {
        firstClickDiv = item;
        break;   
    }

}
// click this div to show up the dropdown
firstClickDiv.click();
var dropdown = firstClickDiv.nextSibling;
dropdown.querySelector("button").click();

var secondClickDiv = false;
for(const item of items) {
    // find the first div that contains the text 24h
    if (item.innerText == "Top 100") {
        secondClickDiv = item;   
        break;
    }

}
secondClickDiv.click();
var secondDropdown = secondClickDiv.nextSibling;
secondDropdown.querySelector("button:nth-of-type(3)").click();
EOF;
    public const TABLE = 'div.sc-1yw69nc-0.DaVcG.table-wrap > div > div:nth-child(2)';
    public const TBODY = 'table.h7vnx2-2.cZkmip.cmc-table > tbody';
    public const PERCENT = 'td:nth-child(4)';
    public const PRICE = 'td:nth-child(3)';

}