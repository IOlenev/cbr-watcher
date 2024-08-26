<?php

namespace App\Domain\Ticker\Feature;

use App\Domain\Rates\Dto\RatesDto;
use App\Domain\Rates\Service\RatesParserInterface;
use App\Domain\Rates\Service\RatesProviderInterface;
use App\Domain\Storage\Service\TickerStorageInterface;
use App\Domain\Ticker\Dto\TickerDto;
use App\Domain\Ticker\Message\IndexRurMessage;
use LogicException;

final class IndexRurFeature
{
    public function __construct(
        private readonly RatesProviderInterface $provider,
        private readonly RatesParserInterface $parser,
        private readonly TickerStorageInterface $storage
    ) {
    }

    public function __invoke(IndexRurMessage $message): void
    {
        if ($message->payload->getTicker()->getBaseCurrency() !== TickerDto::DEFAULT_CURRENCY) {
            throw new LogicException('This feature builds RUR index only');
        }

        $this->storage->withDate($message->payload->getBaseDate());
        $ticker = $this->storage->getTicker(
            $message->payload->getTicker()->getCharCode(),
            $message->payload->getTicker()->getBaseCurrency()
        );
        if (!is_null($ticker?->getDelta())) {
            return;
        }

        /** @var TickerDto[] $tickers */
        $tickers = [];

        //base rates loop
        $this->parser->withRates(RatesDto::create(
            $this->provider->getRates($message->payload->getBaseDate())
        ));
        while ($ticker = $this->parser->getNext()) {
            $tickers[$ticker->getCharCode()] = $ticker;
        }

        //previous rates loop
        $this->parser->withRates(RatesDto::create(
            $this->provider->getRates($message->payload->getPreviousDate())
        ));
        while ($previousDateTicker = $this->parser->getNext()) {
            if (!isset($tickers[$previousDateTicker->getCharCode()])) {
                continue;
            }
            $tickers[$previousDateTicker->getCharCode()]->computeDelta($previousDateTicker->getValue());
            $this->storage->withDate($message->payload->getBaseDate());
            $this->storage->putTicker($tickers[$previousDateTicker->getCharCode()]);

            //warm previous date ticker storage (without delta)
            $this->storage->withDate($message->payload->getPreviousDate());
            $isExists = $this->storage->getTicker(
                $previousDateTicker->getCharCode(),
                TickerDto::DEFAULT_CURRENCY
            );
            if (!is_null($isExists)) {
                continue;
            }
            $this->storage->putTicker($previousDateTicker);
            $this->storage->withDate($message->payload->getBaseDate());
        }
    }
}
