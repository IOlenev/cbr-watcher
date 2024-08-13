<?php

namespace App\Domain\Ticker\Dto;

use App\Dto\DateDto;
use DateTime;
use DateTimeImmutable;

class TickerPayloadDto
{
    private DateDto $previousDate;
    private TickerDto $ticker;

    private function __construct(private DateDto $baseDate)
    {
        $this->previousDate = DateDto::create(
            DateTimeImmutable::createFromMutable(
                (new DateTime($this->baseDate->format('Y-m-d')))->modify('-1 day')
            )
        );
    }

    public static function create(string $charCode, ?DateDto $baseDate = null, string $baseCurrency): static
    {
        if (is_null($baseDate)) {
            $baseDate = DateDto::create();
        }

        return (new static($baseDate))->setTicker(TickerDto::create($charCode, '0', 1, $baseCurrency));
    }

    public function setTicker(TickerDto $ticker): self
    {
        $this->ticker = $ticker;
        return $this;
    }

    public function getTicker(): TickerDto
    {
        return $this->ticker;
    }

    public function getBaseDate(): DateDto
    {
        return $this->baseDate;
    }

    public function getPreviousDate(): DateDto
    {
        return $this->previousDate;
    }
}
