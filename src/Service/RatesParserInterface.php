<?php

namespace App\Service;

use App\Dto\RatesDto;
use App\Dto\TickerDto;

interface RatesParserInterface
{
    public function withRates(RatesDto $rates): self;

    public function getNext(bool $reset = false): ?TickerDto;
}
