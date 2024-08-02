<?php

namespace App\Service;

use App\Dto\TickerDto;

class TickerStorage implements TickerStorageInterface
{
    public function __construct()
    {
    }

    public function getTicker(): ?TickerDto
    {
        // TODO: Implement getTicker() method.
    }

    public function putTicker(TickerDto $ticker): void
    {
        // TODO: Implement putTicker() method.
    }
}
