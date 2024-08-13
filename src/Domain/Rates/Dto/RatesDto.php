<?php

namespace App\Domain\Rates\Dto;

final class RatesDto
{
    private function __construct(
        private readonly string $raw
    ) {
    }

    public static function create(string $raw): self
    {
        return new self($raw);
    }

    public function __toString(): string
    {
        return $this->raw;
    }
}
