<?php

namespace App\Dto;

final class RatesDto
{
    private function __construct(
        readonly private string $raw
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
