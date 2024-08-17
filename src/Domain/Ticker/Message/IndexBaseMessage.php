<?php

namespace App\Domain\Ticker\Message;

use App\Domain\Ticker\Dto\TickerPayloadDto;

readonly class IndexBaseMessage
{
    public function __construct(public TickerPayloadDto $payload) {}
}
