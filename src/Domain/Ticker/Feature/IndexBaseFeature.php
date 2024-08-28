<?php

namespace App\Domain\Ticker\Feature;

use App\Domain\Rates\Dto\RatesDto;
use App\Domain\Rates\Service\RatesParserInterface;
use App\Domain\Rates\Service\RatesProviderInterface;
use App\Domain\Storage\Service\TickerStorageInterface;
use App\Domain\Ticker\Dto\TickerDto;
use App\Domain\Ticker\Message\IndexBaseMessage;
use LogicException;

final class IndexBaseFeature
{
    public function __construct(
        private readonly RatesProviderInterface $provider,
        private readonly RatesParserInterface $parser,
        private readonly TickerStorageInterface $storage
    ) {
    }

    public function __invoke(IndexBaseMessage $message): void
    {
        if ($message->payload->getTicker()->getBaseCurrency() === TickerDto::DEFAULT_CURRENCY) {
            throw new LogicException('This feature builds base index only');
        }

        $this->storage->withDate($message->payload->getBaseDate());
        $ticker = $this->storage->getTicker(
            $message->payload->getTicker()->getCharCode(),
            $message->payload->getTicker()->getBaseCurrency()
        );
        if (!is_null($ticker?->getDelta())) {
            return;
        }

        $tickerRur = $this->storage->getTicker(
            $message->payload->getTicker()->getBaseCurrency(),
            TickerDto::DEFAULT_CURRENCY
        );
        if (is_null($tickerRur)) {
            throw new LogicException('Impossible to build base index without rur index');
        }

        /** @var TickerDto[] $tickers */
        $tickers = [];

        //base rates loop
        $this->parser->withRates(RatesDto::create(
            $this->provider->getRates($message->payload->getBaseDate())
        ));
        while ($ticker = $this->parser->getNext()) {
            $tickerCurrency = TickerDto::create(
                $ticker->getCharCode(),
                $ticker->getValue() * $tickerRur->getKrur(),
                $ticker->getNominal(),
                $message->payload->getTicker()->getBaseCurrency()
            );
            $tickers[$tickerCurrency->getCharCode()] = $tickerCurrency;
        }

        //previous rates loop
        $this->parser->withRates(RatesDto::create(
            $this->provider->getRates($message->payload->getPreviousDate())
        ));
        $this->storage->withDate($message->payload->getPreviousDate());
        $tickerRur = $this->storage->getTicker(
            $message->payload->getTicker()->getBaseCurrency(),
            TickerDto::DEFAULT_CURRENCY
        );
        while ($previousDateTicker = $this->parser->getNext()) {
            if (!isset($tickers[$previousDateTicker->getCharCode()])) {
                continue;
            }
            $tickers[$previousDateTicker->getCharCode()]->computeDelta(
                $previousDateTicker->getValue() * $tickerRur->getKrur()
            );
            $this->storage->withDate($message->payload->getBaseDate());
            $this->storage->putTicker($tickers[$previousDateTicker->getCharCode()]);
        }
    }
}
