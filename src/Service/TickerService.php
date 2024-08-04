<?php

namespace App\Service;

use App\Dto\DateDto;
use App\Dto\TickerDto;
use App\Message\RatesPreloadMessage;
use LogicException;
use Symfony\Component\Messenger\MessageBusInterface;

class TickerService implements TickerServiceInterface
{
    private ?DateDto $date = null;

    public function __construct(
        readonly private TickerStorage $storage,
        readonly private MessageBusInterface $bus
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


    public function getTicker(string $charCode): ?TickerDto
    {
        if (is_null($this->date)) {
            throw new LogicException('Date not specified');
        }

        $ticker = $this->storage->getTicker($charCode);
        if (!is_null($ticker?->getDelta())) {
            return $ticker;
        }

        $this->bus->dispatch(RatesPreloadMessage::create($this->date));
        return null;
    }
}
