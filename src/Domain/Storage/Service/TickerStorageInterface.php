<?php

namespace App\Domain\Storage\Service;

use App\Domain\Ticker\Dto\TickerDto;

interface TickerStorageInterface extends TickerServiceInterface
{
    public function putTicker(TickerDto $ticker): void;

    public function removeTicker(TickerDto $ticker): void;
}
