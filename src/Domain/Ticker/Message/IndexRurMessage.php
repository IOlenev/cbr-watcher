<?php

namespace App\Domain\Ticker\Message;

use App\Domain\Ticker\Dto\TickerPayloadDto;

readonly class IndexRurMessage
{
    public function __construct(public TickerPayloadDto $payload) {}
}
