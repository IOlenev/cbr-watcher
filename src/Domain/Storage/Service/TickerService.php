<?php

namespace App\Domain\Storage\Service;

use App\Domain\Rates\Feature\RatesPreloadFeature;
use App\Domain\Rates\Message\RatesPreloadMessage;
use App\Domain\Ticker\Dto\TickerDto;
use App\Domain\Ticker\Dto\TickerPayloadDto;
use App\Domain\Ticker\Feature\IndexBaseFeature;
use App\Domain\Ticker\Feature\IndexRurFeature;
use App\Domain\Ticker\Message\IndexBaseMessage;
use App\Domain\Ticker\Message\IndexRurMessage;
use App\Dto\DateDto;
use LogicException;
use RuntimeException;

class TickerService implements TickerServiceInterface
{
    private ?DateDto $date = null;

    public function __construct(
        private readonly TickerStorageInterface $storage,
        private readonly RatesPreloadFeature $preloadFeature,
        private readonly IndexRurFeature $featureRur,
        private readonly IndexBaseFeature $featureBase
    ) {
    }

    public function withDate(?DateDto $date = null): TickerServiceInterface
    {
        $date ??= DateDto::create();

        if (!strcmp($date, $this->date)) { //if equal
            return $this;
        }

        $this->date = $date;
        return $this;
    }

    public function getTicker(string $charCode, string $baseCurrency): ?TickerDto
    {
        if (is_null($this->date)) {
            throw new LogicException('Date not specified');
        }

        $ticker = $this->storage
            ->withDate($this->date)
            ->getTicker($charCode, $baseCurrency);
        if (!is_null($ticker?->getDelta())) {
            return $ticker;
        }

        $payload = TickerPayloadDto::create($charCode, $this->date, $baseCurrency);
        $payloadRur = TickerPayloadDto::create($charCode, $this->date);
        ($this->preloadFeature)(new RatesPreloadMessage($payload));
        ($this->featureRur)(new IndexRurMessage($payloadRur));
        if (TickerDto::DEFAULT_CURRENCY !== $baseCurrency) {
            ($this->featureBase)(new IndexBaseMessage($payload));
        }
        $ticker = $this->storage
            ->withDate($this->date)
            ->getTicker($charCode, $baseCurrency);
        if (!is_null($ticker?->getDelta())) {
            return $ticker;
        }

        throw new RuntimeException('Unable to get ticker rates');
    }
}
