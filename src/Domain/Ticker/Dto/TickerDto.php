<?php

namespace App\Domain\Ticker\Dto;

use App\Dto\DateDto;

final class TickerDto
{
    public const DEFAULT_CURRENCY = 'RUR';

    private ?float $delta = null;
    private ?DateDto $date = null;

    private function __construct(
        private string $charCode,
        private float $value,
        private int $nominal,
        private string $baseCurrency
    ) {
    }

    public static function create(
        string $charCode,
        string $value = '0',
        int $nominal = 1,
        string $baseCurrency = self::DEFAULT_CURRENCY
    ): static {
        return new static(
            $charCode,
            (float)preg_replace(['`[^\d.,]`', '`[,]`'], ['', '.'], $value),
            $nominal,
            $baseCurrency
        );
    }

    public function getCharCode(): string
    {
        return $this->charCode;
    }

    public function getBaseCurrency(): string
    {
        return $this->baseCurrency;
    }

    public function getValue(): float
    {
        return $this->value;
    }

    public function getNominal(): int
    {
        return $this->nominal;
    }

    public function computeDelta(float $byPreviousValue): void
    {
        $this->delta = $this->value - $byPreviousValue;
    }

    public function getDelta(): ?float
    {
        return $this->delta;
    }

    public function setDate(DateDto $date): void
    {
        $this->date = $date;
    }

    public function getDate(): ?DateDto
    {
        return $this->date;
    }

    public function getKrur(): float
    {
        return 1 / $this->getValue() * $this->getNominal();
    }

    public function __toString(): string
    {
        return sprintf(
            '%s: %d %s / 1 %s => %01.4f (%01.4f)',
            $this->getDate()?->format('Y-m-d'),
            $this->getNominal(),
            $this->getCharCode(),
            $this->baseCurrency,
            $this->getValue(),
            $this->getDelta()
        );
    }
}
