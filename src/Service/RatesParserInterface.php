<?php

namespace App\Service;

use App\Dto\TickerDto;
use DateTimeInterface;

interface RatesParserInterface
{
    public function withDate(DateTimeInterface $date): self;

    public function getNext(bool $reset = false): ?TickerDto;
}
