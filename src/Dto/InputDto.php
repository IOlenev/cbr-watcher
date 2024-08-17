<?php

namespace App\Dto;

use App\Domain\Ticker\Dto\TickerDto;
use DateTime;
use Symfony\Component\Validator\Constraints as Assert;
use Throwable;

final class InputDto
{
    private function __construct(
        #[Assert\Regex('/^[A-Z]+$/')]
        private string $ticker,
        private string $date,
        #[Assert\Regex('/^[A-Z]+$/')]
        private string $baseCurrency
    ) {
    }

    public static function create(
        string $ticker,
        ?string $date = null,
        ?string $baseCurrency = null
    ): self {
        if (is_null($date)) {
            $date = 'midnight';
        }
        if (is_null($baseCurrency)) {
            $baseCurrency = TickerDto::BASE_CURRENCY;
        }

        return new self(
            strtoupper(trim($ticker)),
            $date,
            strtoupper(trim($baseCurrency))
        );
    }

    #[Assert\IsTrue]
    public function isDate(): bool
    {
        try {
            new DateTime($this->date);
            $result = true;
        } catch (Throwable) {
            $result = false;
        }
        return $result;
    }

    public function getTicker(): string
    {
        return $this->ticker;
    }

    public function getBaseCurrency(): string
    {
        return $this->baseCurrency;
    }

    public function getDate(): string
    {
        return $this->date;
    }
}
