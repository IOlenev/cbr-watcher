<?php

namespace App\Domain\Rates\Message;

use App\Domain\Rates\Feature\RatesPreloadFeature;
use App\Domain\Ticker\Message\IndexRurMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler(fromTransport: "rates_preload")]
final class RatesPreloadHandler
{
    public function __construct(
        private readonly RatesPreloadFeature $feature,
        private readonly MessageBusInterface $bus
    ) {
    }

    public function __invoke(RatesPreloadMessage $message): void
    {
        ($this->feature)($message);
        $this->bus->dispatch(new IndexRurMessage($message->payload));
    }
}
