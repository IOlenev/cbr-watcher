<?php

namespace App\Domain\Ticker\Message;

use App\Domain\Ticker\Dto\TickerDto;
use App\Domain\Ticker\Dto\TickerPayloadDto;
use App\Domain\Ticker\Feature\IndexRurFeature;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler(fromTransport: "index_rur")]
final class IndexRurHandler
{
    public function __construct(
        private readonly IndexRurFeature $feature,
        private readonly MessageBusInterface $bus
    ) {
    }

    public function __invoke(IndexRurMessage $message): void
    {
        $messageRur = new IndexRurMessage(
            TickerPayloadDto::create(
                $message->payload->getTicker()->getCharCode(),
                $message->payload->getBaseDate(),
            TickerDto::BASE_CURRENCY
        ));
        ($this->feature)($messageRur);

        if ($message->payload->getTicker()->getBaseCurrency() !== TickerDto::BASE_CURRENCY) {
            $this->bus->dispatch($message);
        }
    }
}
