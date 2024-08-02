<?php

namespace App\Dto;

class TickerDto
{
    private ?float $delta = null;

    private function __construct(
        private string $charCode,
        private float $value,
        private int $nominal
    ) {
    }

    public static function create(string $charCode, string $value, int $nominal): static
    {
        return new static(
            $charCode,
            (float)preg_replace(['`[^\d.,]`', '`[,]`'], ['', '.'], $value),
            $nominal
        );
    }

    public function getCharCode(): string
    {
        return $this->charCode;
    }

    public function getValue(): float
    {
        return $this->value;
    }

    public function getNominal(): int
    {
        return $this->nominal;
    }

    public function getDelta(): ?float
    {
        return $this->delta;
    }
}
