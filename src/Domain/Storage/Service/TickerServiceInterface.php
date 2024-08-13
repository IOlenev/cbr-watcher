<?php

namespace App\Domain\Storage\Service;

use App\Domain\Ticker\Dto\TickerDto;
use App\Dto\DateDto;

interface TickerServiceInterface
{
    public function withDate(?DateDto $date = null): self;

    public function getTicker(string $charCode, string $baseCurrency): ?TickerDto;

}
