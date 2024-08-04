<?php

namespace App\Service;

use App\Dto\TickerDto;

interface TickerStorageInterface extends TickerServiceInterface
{
    public function putTicker(TickerDto $ticker): void;
}
