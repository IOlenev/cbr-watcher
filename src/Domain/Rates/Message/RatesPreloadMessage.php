<?php

namespace App\Domain\Rates\Message;

use App\Domain\Ticker\Dto\TickerPayloadDto;

readonly class RatesPreloadMessage
{
    public function __construct(public TickerPayloadDto $payload) {}
}
