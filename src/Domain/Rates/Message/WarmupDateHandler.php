<?php

namespace App\Domain\Rates\Message;

use App\Domain\Ticker\Dto\TickerPayloadDto;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler(fromTransport: "warmup_date")]
final class WarmupDateHandler
{
    public function __construct(
        private readonly MessageBusInterface $bus
    ) {
    }

    public function __invoke(WarmupDateMessage $message): void
    {
        foreach ($message->tickers as $ticker) {
            foreach ($message->tickers as $baseTicker) {
                $this->bus->dispatch(new RatesPreloadMessage(
                    TickerPayloadDto::create($ticker, $message->date, $baseTicker)
                ));
            }
        }
    }
}
