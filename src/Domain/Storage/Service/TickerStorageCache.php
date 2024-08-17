<?php

namespace App\Domain\Storage\Service;

use App\Domain\Ticker\Dto\TickerDto;
use App\Dto\DateDto;
use Psr\Cache\CacheItemPoolInterface;
use RuntimeException;

class TickerStorageCache implements TickerStorageInterface
{
    private ?DateDto $date = null;

    public function __construct(private readonly CacheItemPoolInterface $tickerCache)
    {
    }

    private function getKey(string $charCode, string $baseCurrency): string
    {
        return sprintf('%s_%s_%s', $this->date->format('Ymd'), $charCode, $baseCurrency);
    }

    public function getTicker(string $charCode, string $baseCurrency): ?TickerDto
    {
        $item = $this->tickerCache->getItem($this->getKey($charCode, $baseCurrency));
        $value = $item->get();
        if (is_null($value) || $value instanceof TickerDto) {
            return $value;
        }

        throw new RuntimeException('Ticker cache item wrong type retrived');
    }

    public function putTicker(TickerDto $ticker): void
    {
        $item = $this->tickerCache->getItem($this->getKey($ticker->getCharCode(), $ticker->getBaseCurrency()));
        $ticker->setDate($this->date);
        $item->set($ticker);
        $this->tickerCache->save($item);
    }

    public function removeTicker(TickerDto $ticker): void
    {
        $this->tickerCache->deleteItem($this->getKey($ticker->getCharCode(), $ticker->getBaseCurrency()));
    }

    public function withDate(DateDto $date = null): TickerStorageInterface
    {
        $this->date = $date;
        return $this;
    }
}
