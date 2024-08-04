<?php

namespace App\Service;

use App\Dto\DateDto;
use App\Dto\TickerDto;

class TickerStorage implements TickerStorageInterface
{
    private ?DateDto $date = null;

    public function __construct()
    {
    }

    public function getTicker(string $charCode): ?TickerDto
    {
        // TODO: Implement getTicker() method.
    }

    public function putTicker(TickerDto $ticker): void
    {
        // TODO: Implement putTicker() method.
    }

    public function withDate(DateDto $date = null): TickerStorageInterface
    {
        // TODO: Implement withDate() method.
    }
}
