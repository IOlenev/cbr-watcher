<?php

namespace App\Service;

use App\Dto\TickerDto;

interface TickerStorageInterface
{
    public function getTicker(): ?TickerDto;

    public function putTicker(TickerDto $ticker): void;
}
