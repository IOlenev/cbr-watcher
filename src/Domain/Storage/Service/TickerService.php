<?php

namespace App\Domain\Storage\Service;

use App\Domain\Rates\Message\RatesPreloadMessage;
use App\Domain\Ticker\Dto\TickerDto;
use App\Domain\Ticker\Dto\TickerPayloadDto;
use App\Dto\DateDto;
use LogicException;
use Symfony\Component\Messenger\MessageBusInterface;

class TickerService implements TickerServiceInterface
{
    private ?DateDto $date = null;

    public function __construct(
        private readonly TickerStorageInterface $storage,
        private readonly MessageBusInterface $bus
    ) {
    }

    public function withDate(?DateDto $date = null): TickerServiceInterface
    {
        if (is_null($date)) {
            $date = DateDto::create();
        }

        if (!strcmp($date, $this->date)) { //if equal
            return $this;
        }

        $this->date = $date;
        $this->storage->withDate($this->date);
        return $this;
    }


    public function getTicker(string $charCode, string $baseCurrency): ?TickerDto
    {
        if (is_null($this->date)) {
            throw new LogicException('Date not specified');
        }

        $ticker = $this->storage->getTicker($charCode, $baseCurrency);
        if (!is_null($ticker?->getDelta())) {
            return $ticker;
        }

        $this->bus->dispatch(new RatesPreloadMessage(
            TickerPayloadDto::create($charCode, $this->date, $baseCurrency))
        );
        return null;
    }
}
