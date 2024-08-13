<?php

namespace App\Domain\Rates\Service;

use App\Domain\Rates\Dto\RatesDto;
use App\Domain\Ticker\Dto\TickerDto;

interface RatesParserInterface
{
    public function withRates(RatesDto $rates): self;

    public function getNext(bool $reset = false): ?TickerDto;
}
